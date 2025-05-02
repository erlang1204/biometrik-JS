import cv2
import os
from tkinter import Tk, Label, Entry, Button, messagebox

def capture_images(name):
    save_path = os.path.join("dataset", name)

    if not os.path.exists(save_path):
        os.makedirs(save_path)

    cam = cv2.VideoCapture(0)
    cv2.namedWindow("Registrasi Wajah - Tekan 'q' untuk batal")

    count = 0
    while True:
        ret, frame = cam.read()
        if not ret:
            break

        cv2.imshow("Registrasi Wajah - Tekan 'q' untuk batal", frame)
        k = cv2.waitKey(1)

        if count < 20:
            img_path = os.path.join(save_path, f"{str(count)}.jpg")
            cv2.imwrite(img_path, frame)
            count += 1
        else:
            break

        if k % 256 == 113:  # 'q' untuk keluar
            break

    cam.release()
    cv2.destroyAllWindows()
    messagebox.showinfo("Selesai", f"Registrasi wajah untuk '{name}' selesai. Total gambar: {count}")

def start_registration():
    name = name_entry.get().strip()
    if name == "":
        messagebox.showwarning("Peringatan", "Nama tidak boleh kosong!")
        return

    capture_images(name)

# UI dengan Tkinter
window = Tk()
window.title("Registrasi Biometrik Wajah")
window.geometry("400x200")

Label(window, text="Masukkan Nama:").pack(pady=10)
name_entry = Entry(window, width=30)
name_entry.pack(pady=5)

Button(window, text="Mulai Registrasi", command=start_registration).pack(pady=20)

window.mainloop()
