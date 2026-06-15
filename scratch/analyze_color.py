import os
from PIL import Image
from collections import Counter

def get_dominant_colors(image_path, num_colors=10):
    if not os.path.exists(image_path):
        print(f"File not found: {image_path}")
        return
    
    img = Image.open(image_path)
    img = img.convert('RGB')
    img = img.resize((200, 200)) # resize to speed up
    
    pixels = list(img.getdata())
    counter = Counter(pixels)
    
    print("Most common colors (RGB and HEX):")
    for rgb, count in counter.most_common(num_colors):
        hex_color = '#{:02x}{:02x}{:02x}'.format(*rgb)
        print(f"{hex_color}: {count} pixels ({rgb})")

# Let's inspect the screenshots from today
screenshot_dir = "/home/kaarr/Pictures/Screenshots"
files = sorted([f for f in os.listdir(screenshot_dir) if f.startswith("Screenshot from 2026-06-08")], reverse=True)

for f in files[:3]:
    path = os.path.join(screenshot_dir, f)
    print(f"\nAnalyzing: {f}")
    get_dominant_colors(path)
