# USAGE
# python pelatihan_dataset.py --embeddings output/embeddings.pickle --recognizer output/recognizer.pickle --le output/le.pickle

from sklearn.preprocessing import LabelEncoder
from sklearn.svm import SVC
import argparse
import pickle

ap = argparse.ArgumentParser()
ap.add_argument("-e", "--embeddings", required=True)
ap.add_argument("-r", "--recognizer", required=True)
ap.add_argument("-l", "--le", required=True)
args = vars(ap.parse_args())

print("[INFO] Memuat embeddings...")
data = pickle.loads(open(args["embeddings"], "rb").read())

print("[INFO] Mengkodekan label...")
le = LabelEncoder()
labels = le.fit_transform(data["names"])

if len(set(labels)) < 2:
    print("[ERROR] Minimal harus ada 2 kelas wajah yang berbeda di dataset!")
    exit()

print("[INFO] Melatih model...")
recognizer = SVC(C=1.0, kernel="linear", probability=True)
recognizer.fit(data["embeddings"], labels)

f = open(args["recognizer"], "wb")
f.write(pickle.dumps(recognizer))
f.close()

f = open(args["le"], "wb")
f.write(pickle.dumps(le))
f.close()
