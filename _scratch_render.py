import sys

import fitz

path = sys.argv[1]
out = sys.argv[2]

doc = fitz.open(path)
for index, page in enumerate(doc, start=1):
    pix = page.get_pixmap(dpi=110)
    pix.save(f"{out}/page{index}.png")
    print(f"page{index}.png {pix.width}x{pix.height}")
