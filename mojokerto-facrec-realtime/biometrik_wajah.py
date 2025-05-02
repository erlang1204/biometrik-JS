import os
import cv2
import pickle
import time
import imutils
import numpy as np
from imutils.video import VideoStream
from sklearn.preprocessing import LabelEncoder
from sklearn.svm import SVC

# Path
DATASET_PATH = "dataset"
OUTPUT_PATH = "output"
DETECTOR_PATH = "detector"
EMBEDDING_MODEL_PATH = "openface_nn4.small2.v1.t7"
TIDAK_DIKENALI_PATH = "tidak_dikenali"

# Buat folder output jika belum ada
os.makedirs(OUTPUT_PATH, exist_ok=True)
os.makedirs(TIDAK_DIKENALI_PATH, exist_ok=True)

print("[INFO] Memuat model deteksi wajah dan embedding...")
detector = cv2.dnn.readNetFromCaffe(
    os.path.join(DETECTOR_PATH, "deploy.prototxt"),
    os.path.join(DETECTOR_PATH, "res10_300x300_ssd_iter_140000.caffemodel")
)
embedder = cv2.dnn.readNetFromTorch(EMBEDDING_MODEL_PATH)

# Proses pembuatan embeddings
print("[INFO] Membuat embeddings dari dataset...")
knownEmbeddings = []
knownNames = []
total = 0

for person_name in os.listdir(DATASET_PATH):
    person_folder = os.path.join(DATASET_PATH, person_name)
    if not os.path.isdir(person_folder):
        continue

    for image_name in os.listdir(person_folder):
        image_path = os.path.join(person_folder, image_name)
        image = cv2.imread(image_path)
        if image is None:
            continue

        image = imutils.resize(image, width=600)
        (h, w) = image.shape[:2]
        imageBlob = cv2.dnn.blobFromImage(
            cv2.resize(image, (300, 300)), 1.0, (300, 300),
            (104.0, 177.0, 123.0), swapRB=False, crop=False
        )

        detector.setInput(imageBlob)
        detections = detector.forward()

        if len(detections) > 0:
            i = np.argmax(detections[0, 0, :, 2])
            confidence = detections[0, 0, i, 2]

            if confidence > 0.5:
                box = detections[0, 0, i, 3:7] * np.array([w, h, w, h])
                (startX, startY, endX, endY) = box.astype("int")
                face = image[startY:endY, startX:endX]
                (fH, fW) = face.shape[:2]

                if fW < 20 or fH < 20:
                    continue

                faceBlob = cv2.dnn.blobFromImage(face, 1.0 / 255,
                    (96, 96), (0, 0, 0), swapRB=True, crop=False)
                embedder.setInput(faceBlob)
                vec = embedder.forward()

                knownNames.append(person_name)
                knownEmbeddings.append(vec.flatten())
                total += 1

print(f"[INFO] Total embeddings: {total}")

print("[INFO] Menyimpan embeddings...")
data = {"embeddings": knownEmbeddings, "names": knownNames}
with open(os.path.join(OUTPUT_PATH, "embeddings.pickle"), "wb") as f:
    f.write(pickle.dumps(data))

print("[INFO] Melatih model SVM...")
le = LabelEncoder()
labels = le.fit_transform(knownNames)
recognizer = SVC(C=1.0, kernel="linear", probability=True)
recognizer.fit(knownEmbeddings, labels)

with open(os.path.join(OUTPUT_PATH, "recognizer.pickle"), "wb") as f:
    f.write(pickle.dumps(recognizer))
with open(os.path.join(OUTPUT_PATH, "le.pickle"), "wb") as f:
    f.write(pickle.dumps(le))

# Real-time face recognition
print("[INFO] Memulai pengenalan wajah secara realtime...")
vs = VideoStream(src=0).start()
time.sleep(2.0)

while True:
    frame = vs.read()
    frame = imutils.resize(frame, width=600)
    (h, w) = frame.shape[:2]
    imageBlob = cv2.dnn.blobFromImage(
        cv2.resize(frame, (300, 300)), 1.0, (300, 300),
        (104.0, 177.0, 123.0), swapRB=False, crop=False
    )

    detector.setInput(imageBlob)
    detections = detector.forward()

    for i in range(0, detections.shape[2]):
        confidence = detections[0, 0, i, 2]

        if confidence > 0.5:
            box = detections[0, 0, i, 3:7] * np.array([w, h, w, h])
            (startX, startY, endX, endY) = box.astype("int")
            face = frame[startY:endY, startX:endX]
            (fH, fW) = face.shape[:2]

            if fW < 20 or fH < 20:
                continue

            faceBlob = cv2.dnn.blobFromImage(face, 1.0 / 255,
                (96, 96), (0, 0, 0), swapRB=True, crop=False)
            embedder.setInput(faceBlob)
            vec = embedder.forward()

            preds = recognizer.predict_proba(vec)[0]
            j = np.argmax(preds)
            proba = preds[j]
            name = le.classes_[j] if proba > 0.5 else "tidak dikenali"

            text = f"{name}: {proba * 100:.2f}%" if name != "tidak dikenali" else "tidak dikenali"
            y = startY - 10 if startY - 10 > 10 else startY + 10
            color = (0, 255, 0) if name != "tidak dikenali" else (0, 0, 255)

            cv2.rectangle(frame, (startX, startY), (endX, endY), color, 2)
            cv2.putText(frame, text, (startX, y), cv2.FONT_HERSHEY_SIMPLEX, 0.45, color, 2)

            if name == "tidak dikenali":
                filename = f"{int(time.time())}.jpg"
                filepath = os.path.join(TIDAK_DIKENALI_PATH, filename)
                cv2.imwrite(filepath, face)

    cv2.imshow("Pengenalan Wajah", frame)
    key = cv2.waitKey(1) & 0xFF
    if key == ord("q"):
        break

cv2.destroyAllWindows()
vs.stop()
