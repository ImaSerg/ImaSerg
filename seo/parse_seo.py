import json, re, glob, os

SRC = r"C:\Users\admin\Desktop\Site\Текст\SEO text for megamenu\files"
OUT = os.path.join(os.path.dirname(os.path.abspath(__file__)), "seo_pages.json")


def sections(text):
    parts = re.split(r"^=== (.+?) ===\s*$", text, flags=re.M)
    head, rest = parts[0], parts[1:]
    sec = {}
    for i in range(0, len(rest), 2):
        sec[rest[i].split(" (")[0].strip()] = rest[i + 1].strip()
    return head, sec


def blocks(md):
    """Markdown-lite -> list of blocks: ['h', text] | ['p', text] | ['ul', [items]]."""
    out = []
    for chunk in re.split(r"\n\s*\n", md.strip()):
        lines = [l.rstrip() for l in chunk.split("\n") if l.strip()]
        para = []

        def flush():
            if para:
                out.append(["p", " ".join(para)])
                para.clear()

        for l in lines:
            if l.startswith("## "):
                flush()
                out.append(["h", l[3:].strip()])
            elif l.startswith("- "):
                flush()
                if out and out[-1][0] == "ul":
                    out[-1][1].append(l[2:].strip())
                else:
                    out.append(["ul", [l[2:].strip()]])
            else:
                para.append(l.strip())
        flush()
    return out


def parse(path):
    text = open(path, encoding="utf-8").read()
    head, sec = sections(text)
    meta = {}
    for l in head.splitlines():
        m = re.match(r"^(СТРАНИЦА|URL|Title|Description|H1|Основной запрос|Дополнительные запросы):\s*(.*)$", l)
        if m:
            meta[m.group(1)] = m.group(2).strip()
    slug = meta["URL"].rstrip("/").split("/")[-1]
    faq = re.findall(r"^В:\s*(.+?)\s*\nО:\s*(.+?)\s*$", sec["FAQ"], flags=re.M)
    links = re.findall(r"^-\s*«(.+?)»\s*→\s*(\S+)", sec["ВНУТРЕННИЕ ССЫЛКИ"], flags=re.M)
    cta = re.sub(r"\s*Телефон:\s*\+7[\d\s()\-]+\.?\s*$", "", sec["ПРИЗЫВ К ДЕЙСТВИЮ"]).strip()
    kw = [meta.get("Основной запрос", "")] + [k.strip() for k in meta.get("Дополнительные запросы", "").split(",")]
    return {
        "file": os.path.basename(path),
        "slug": slug,
        "name": meta["СТРАНИЦА"],
        "title": meta["Title"],
        "description": meta["Description"],
        "h1": meta["H1"],
        "keywords": ", ".join(k for k in kw if k),
        "intro": sec["ОПИСАНИЕ"].replace("\n", " ").strip(),
        "main": blocks(sec["ОСНОВНОЙ ТЕКСТ"]),
        "faq": [list(x) for x in faq],
        "cta": cta,
        "links": [list(x) for x in links],
    }


pages = [parse(p) for p in sorted(glob.glob(os.path.join(SRC, "*.txt")))]
json.dump(pages, open(OUT, "w", encoding="utf-8"), ensure_ascii=False, indent=1)
for p in pages:
    nh = sum(1 for b in p["main"] if b[0] == "h")
    print(p["slug"], "| faq", len(p["faq"]), "| h", nh, "| links", len(p["links"]), "| title", len(p["title"]), "| desc", len(p["description"]), "| cta", len(p["cta"]))
print(len(pages), "pages ->", OUT)
