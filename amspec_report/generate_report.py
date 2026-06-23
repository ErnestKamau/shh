"""
AmSpec Laboratory Test Report — ReportLab PDF Generator
========================================================
Usage:
    python generate_report.py

Output:
    amspec_lab_report.pdf  (in the same directory)

All dynamic values live in `report_data` at the bottom of this file.
"""

import os
import math
from reportlab.lib.pagesizes import A4
from reportlab.lib import colors
from reportlab.lib.styles import ParagraphStyle
from reportlab.lib.enums import TA_LEFT, TA_CENTER, TA_RIGHT, TA_JUSTIFY
from reportlab.platypus import (
    SimpleDocTemplate, Paragraph, Spacer, Table, TableStyle,
    HRFlowable, KeepTogether, PageBreak, Image
)
from reportlab.pdfgen import canvas as rl_canvas

# ─────────────────────────────────────────────────────────────
# CONSTANTS
# ─────────────────────────────────────────────────────────────
PAGE_W, PAGE_H = A4          # 595.27 x 841.89 pt
ML = 50                      # margin left
MR = 50                      # margin right
MT = 40                      # margin top
MB = 60                      # margin bottom (footer area)
BW = PAGE_W - ML - MR        # body width ~495

# Colours
C_RED    = colors.HexColor("#8B1A1A")
C_GREY   = colors.HexColor("#F2F2F2")
C_SECT   = colors.HexColor("#EEEEEE")
C_ALT    = colors.HexColor("#F9F9F9")
C_BLACK  = colors.black
C_WHITE  = colors.white
C_BORDER = colors.HexColor("#AAAAAA")

ASSETS_DIR = os.path.join(os.path.dirname(__file__), "assets")


# ─────────────────────────────────────────────────────────────
# ASSET HELPER
# ─────────────────────────────────────────────────────────────
def asset_path(name):
    p = os.path.join(ASSETS_DIR, name)
    return p if os.path.isfile(p) else None


# ─────────────────────────────────────────────────────────────
# LOW-LEVEL CANVAS HELPERS
# ─────────────────────────────────────────────────────────────
def draw_hexagon(c, cx, cy, r):
    c.saveState()
    c.setStrokeColor(C_RED)
    c.setFillColor(C_WHITE)
    for radius, lw in [(r, 1.8), (r * 0.70, 1.1)]:
        path = c.beginPath()
        for i in range(6):
            angle = math.radians(90 + i * 60)
            x = cx + radius * math.cos(angle)
            y = cy + radius * math.sin(angle)
            if i == 0:
                path.moveTo(x, y)
            else:
                path.lineTo(x, y)
        path.close()
        c.setLineWidth(lw)
        c.drawPath(path, stroke=1, fill=0)
    c.setFillColor(C_RED)
    c.circle(cx, cy, r * 0.22, stroke=0, fill=1)
    c.restoreState()


def draw_header(c, rd, page_num):
    """Draw the repeated page header: logo left, cert/page right, separator."""
    c.saveState()
    y_top = PAGE_H - MT
    x_r   = PAGE_W - MR

    # Logo
    lp = asset_path("amspec_logo.png")
    if lp:
        c.drawImage(lp, ML, y_top - 42, width=130, height=42,
                    preserveAspectRatio=True, mask="auto")
    else:
        c.setFont("Helvetica-Bold", 20)
        c.setFillColor(C_RED)
        c.drawString(ML, y_top - 30, "AmSpec")

    # Hexagon graphic
    hex_p = asset_path("hexagon_graphic.png")
    if hex_p:
        c.drawImage(hex_p, x_r - 52, y_top - 52, width=52, height=52,
                    preserveAspectRatio=True, mask="auto")
    else:
        draw_hexagon(c, x_r - 26, y_top - 26, 24)

    # Cert + page text (right of text, left of hexagon)
    c.setFillColor(C_BLACK)
    c.setFont("Helvetica", 9)
    txt_x = x_r - 58
    c.drawRightString(txt_x, y_top - 16, f"Certificate no.: {rd['certificate_no']}")
    c.drawRightString(txt_x, y_top - 29, f"Page {page_num} of {rd['page_total']}")

    # Separator
    c.setStrokeColor(C_BLACK)
    c.setLineWidth(1)
    c.line(ML, y_top - 48, x_r, y_top - 48)
    c.restoreState()


def draw_footer(c, rd):
    """Draw the repeated page footer: disclaimer text + QR code."""
    from reportlab.lib.utils import simpleSplit
    c.saveState()
    qr_p  = asset_path("qr_code.png")
    qr_w  = qr_h = 42
    x_r   = PAGE_W - MR
    text_right = x_r - (qr_w + 6) if qr_p else x_r
    footer = (
        "This document is issued by the Company subject to the Terms and Conditions at "
        "https://www.amspecgroup.com/terms-conditions. Any holder of this document is advised that "
        "information contained herein reflects the Company\u2019s findings at the time and place of its "
        "intervention only and within the scope of the Client\u2019s instructions. The Company\u2019s sole "
        "responsibility is to its Client and the Company disclaims any liability to third parties. "
        "Any alteration, forgery or falsification of the content or appearance of this document is unlawful."
    )
    lines = simpleSplit(footer, "Helvetica", 6, text_right - ML)
    # position footer lines just above bottom margin
    y = MB - 4
    c.setFont("Helvetica", 6)
    c.setFillColor(colors.HexColor("#333333"))
    for line in lines:
        c.drawString(ML, y, line)
        y -= 7.5

    if qr_p:
        c.drawImage(qr_p, x_r - qr_w, MB - qr_h, width=qr_w, height=qr_h,
                    preserveAspectRatio=True, mask="auto")
    c.restoreState()


# ─────────────────────────────────────────────────────────────
# PARAGRAPH / TABLE CELL HELPERS
# ─────────────────────────────────────────────────────────────
def P(text, font="Helvetica", size=8, color=C_BLACK, align=TA_LEFT, leading=None):
    if leading is None:
        leading = size + 2
    return Paragraph(text, ParagraphStyle(
        "p", fontName=font, fontSize=size, leading=leading,
        textColor=color, alignment=align,
        spaceAfter=0, spaceBefore=0))


def Pb(text, size=8, **kw):
    return P(text, font="Helvetica-Bold", size=size, **kw)


def Pw(text, size=8):
    return P(text, font="Helvetica-Bold", size=size, color=C_WHITE)


def make_table(data, col_widths, style_cmds, repeat_rows=0):
    t = Table(data, colWidths=col_widths, repeatRows=repeat_rows)
    base = [
        ("VALIGN",        (0, 0), (-1, -1), "MIDDLE"),
        ("TOPPADDING",    (0, 0), (-1, -1), 3),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 3),
        ("LEFTPADDING",   (0, 0), (-1, -1), 4),
        ("RIGHTPADDING",  (0, 0), (-1, -1), 4),
    ]
    t.setStyle(TableStyle(base + style_cmds))
    return t


# ─────────────────────────────────────────────────────────────
# BUILD STORY (all pages as a single Platypus story)
# ─────────────────────────────────────────────────────────────
def build_story(rd):
    story = []

    # ══════════════════════════════════════════════════════════
    # PAGE 1
    # ══════════════════════════════════════════════════════════

    # ── Report title ─────────────────────────────────────────
    story.append(Spacer(1, 10))
    story.append(P("<u><b>LABORATORY TEST REPORT</b></u>",
                   font="Helvetica-Bold", size=11, align=TA_CENTER))
    story.append(Spacer(1, 10))

    # ── Client info (borderless, colon-separated) ─────────────
    ci = [
        [Pb("Attention"),            P(":"), P(rd["attention"],  size=8.5)],
        [Pb("Client"),               P(":"), P(rd["client"],     size=8.5)],
        [Pb("Address and Location"), P(":"), P(rd["address"],    size=8.5)],
    ]
    ci_t = make_table(ci, [130, 8, BW - 138], [
        ("GRID",   (0, 0), (-1, -1), 0, C_WHITE),
        ("TOPPADDING",    (0, 0), (-1, -1), 2),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 2),
        ("LEFTPADDING",   (0, 0), (-1, -1), 0),
    ])
    story.append(ci_t)
    story.append(Spacer(1, 8))

    # ── Report meta grid (4 cols: label|value|label|value) ───
    cw4 = [100, 115, 115, BW - 330]
    def ml(t): return Pb(t, size=8)
    def mv(t): return P(t,  size=8)

    meta = [
        [ml("Report No."),         mv(rd["report_no"]),          ml("Sample No."),          mv(rd["sample_no"])],
        [ml("Date received"),      mv(rd["date_received"]),       ml("Date Reported"),       mv(rd["date_reported"])],
        [ml("Container Type"),     mv(rd["container_type"]),      ml("Sample Description"),  mv(rd["sample_description"])],
        [ml("Weight"),             mv(rd["weight"]),              ml("Sampled By"),          mv(rd["sampled_by"])],
        [ml("Sample Temperature"), mv(rd["sample_temperature"]),  ml("Sample Preservation"), mv(rd["sample_preservation"])],
        [ml("Production Date"),    mv(rd["production_date"]),     ml("Expiry Date"),         mv(rd["expiry_date"])],
        [ml("Lot No."),            mv(rd["lot_no"]),              ml("No. of pages"),        mv(rd["no_of_pages"])],
        [ml("Date of Analysis"),   mv(rd["date_of_analysis"]),    "",                        ""],
    ]
    meta_t = make_table(meta, cw4, [
        ("GRID",       (0, 0), (-1, -1), 0.5, C_BLACK),
        ("BACKGROUND", (0, 0), (0, -1),  C_GREY),
        ("BACKGROUND", (2, 0), (2, -2),  C_GREY),
        ("SPAN",       (1, 7), (3, 7)),
        ("BACKGROUND", (2, 7), (3, 7),   C_WHITE),
    ])
    story.append(meta_t)
    story.append(Spacer(1, 8))

    # ── Analysis results table ────────────────────────────────
    col_w = [163, 72, 33, 45, 35, BW - 348]

    def ah(t): return Pw(t, size=8)
    def av(t): return P(t,  size=8)
    def av_sh(t): return Pb(t, size=8.5)

    hdr_row = [ah("Analysis"), ah("Results"), ah("Unit"),
               ah("LOD/LOQ"), ah("M.U%"),    ah("Method of Analysis")]

    rows        = [hdr_row]
    style_extra = []
    ri          = 1

    for entry in rd["analyses"]:
        if entry.get("type") == "section_header":
            rows.append([av_sh(entry["name"]), "", "", "", "", ""])
            style_extra += [
                ("SPAN",       (0, ri), (-1, ri)),
                ("BACKGROUND", (0, ri), (-1, ri), C_SECT),
            ]
        else:
            bg = C_WHITE if ri % 2 == 0 else C_ALT
            rows.append([
                av(entry["name"]),
                av(entry.get("result", "")),
                av(entry.get("unit", "")),
                av(entry.get("lod", "-")),
                av(entry.get("mu", "-")),
                av(entry.get("method", "")),
            ])
            style_extra.append(("BACKGROUND", (0, ri), (-1, ri), bg))
        ri += 1

    res_t = make_table(rows, col_w, [
        ("GRID",          (0, 0), (-1, -1), 0.5, C_BORDER),
        ("BACKGROUND",    (0, 0), (-1, 0),  C_RED),
        ("TEXTCOLOR",     (0, 0), (-1, 0),  C_WHITE),
        ("TOPPADDING",    (0, 0), (-1, -1), 2),
        ("BOTTOMPADDING", (0, 0), (-1, -1), 2),
    ] + style_extra, repeat_rows=1)
    story.append(res_t)

    # ══════════════════════════════════════════════════════════
    # PAGE 2
    # ══════════════════════════════════════════════════════════
    story.append(PageBreak())

    # ── *Accredited Analysis ──────────────────────────────────
    story.append(Spacer(1, 8))
    story.append(Pb("*Accredited Analysis", size=9))
    story.append(Spacer(1, 8))

    # ── Remarks paragraph ────────────────────────────────────
    remarks = (
        "<b>Remarks:</b> The dough elasticity and stretchability are evaluated using the "
        "<b><i>Alveograph</i></b> and <b><i>Extensograph</i></b> analyses, where the "
        "<b><i>Alveograph</i></b> measures extensibility through the "
        "<b><i>&#34;L&#34; value</i></b>, the balance between tenacity and extensibility "
        "through the <b><i>&#34;P/L&#34; ratio</i></b>, and elasticity through the "
        "<b><i>&#34;Ie&#34; (Elasticity Index)</i></b> parameter, while the "
        "<b><i>Extensograph</i></b> directly measures dough stretching capacity and elastic "
        "tolerance through the <b><i>&#34;Extensibility [mm]&#34;</i></b> parameter at "
        "different resting intervals."
    )
    story.append(Paragraph(remarks, ParagraphStyle(
        "rem", fontName="Helvetica", fontSize=8.5, leading=13,
        alignment=TA_JUSTIFY, spaceAfter=10, spaceBefore=0)))

    # ── Analysis conducted / test method row ─────────────────
    acby = rd.get("analysis_conducted_by", "Subcontract Lab")
    tmd  = rd.get("test_method_deviation",  "None")
    two_row = make_table(
        [[P(f"Analysis conducted by: {acby}"), P(f"Test method deviation: {tmd}")]],
        [BW / 2, BW / 2],
        [("GRID", (0, 0), (-1, -1), 0.5, C_BLACK),
         ("TOPPADDING",    (0, 0), (-1, -1), 5),
         ("BOTTOMPADDING", (0, 0), (-1, -1), 5),
         ("LEFTPADDING",   (0, 0), (-1, -1), 5),
         ("RIGHTPADDING",  (0, 0), (-1, -1), 5)])
    story.append(two_row)
    story.append(Spacer(1, 12))

    # ── Signatory intro ───────────────────────────────────────
    company_name = rd.get("company_name", "AmSpec Inspection &amp; Testing Services")
    story.append(Pb(f"Signed for and on behalf of {company_name}", size=9))
    story.append(Spacer(1, 10))

    # ── Signatory two-column block ────────────────────────────
    sig_name    = rd.get("signatory_name",    "Mohammad Ziya Zaidi")
    sig_title   = rd.get("signatory_title",   "Technical Manager | UAE")
    sig_company = rd.get("signatory_company", "AMSPEC MIDDLE EAST")

    sig_p   = asset_path("signature_zaidi.png")
    logo_p  = asset_path("amspec_logo.png")
    sig_img = Image(sig_p,  width=90, height=45) if sig_p  else P(f"[signature]", size=7, color=colors.grey)
    logo_img= Image(logo_p, width=110, height=44) if logo_p else P(f"[logo]",     size=7, color=colors.grey)

    left_items = [
        Pb(sig_name, size=9),
        Spacer(1, 6),
        HRFlowable(width=200, thickness=0.8, color=C_BLACK),
        Spacer(1, 3),
        P(sig_title,   size=8),
        P(sig_company, size=8),
    ]
    right_items = [
        sig_img,
        Spacer(1, 4),
        logo_img,
        Paragraph(f"<i>{sig_name}</i>", ParagraphStyle(
            "cap", fontName="Helvetica-Oblique", fontSize=7,
            alignment=TA_CENTER, leading=9)),
    ]

    sig_block = make_table(
        [[left_items, right_items]],
        [BW * 0.55, BW * 0.45],
        [("VALIGN",        (0, 0), (-1, -1), "TOP"),
         ("GRID",          (0, 0), (-1, -1), 0, C_WHITE),
         ("LEFTPADDING",   (0, 0), (-1, -1), 0),
         ("RIGHTPADDING",  (0, 0), (-1, -1), 0),
         ("TOPPADDING",    (0, 0), (-1, -1), 0),
         ("BOTTOMPADDING", (0, 0), (-1, -1), 0)])
    story.append(sig_block)
    story.append(Spacer(1, 14))

    # ── Legal notices ─────────────────────────────────────────
    story.append(P("Test results relate only to the samples tested.", size=8))
    story.append(Spacer(1, 3))
    story.append(P("This report shall not be reproduced except in full, without the written approval of the Laboratory.", size=8))

    # ── End of text ───────────────────────────────────────────
    story.append(Spacer(1, 10))
    story.append(P("-End of text", size=8, align=TA_CENTER))

    # ── Issued date ───────────────────────────────────────────
    story.append(Spacer(1, 8))
    story.append(P(f"Issued on {rd.get('issued_date', '')}.", size=8))

    # ── Company tag ───────────────────────────────────────────
    story.append(Spacer(1, 6))
    story.append(Pb(rd.get("company_tag", "AMSPEC FIRST CLASS SUPERINTENDENT COMPANY"),
                    size=9, align=TA_RIGHT))

    # ── Disclaimer ────────────────────────────────────────────
    story.append(Spacer(1, 10))
    story.append(Paragraph(
        "<i>DISCLAIMER: ALL THE SAMPLES WERE TESTED AT A THIRD-PARTY LABORATORY</i>",
        ParagraphStyle("disc", fontName="Helvetica-Oblique", fontSize=8,
                       alignment=TA_CENTER, leading=11)))

    return story


# ─────────────────────────────────────────────────────────────
# MAIN PDF BUILDER
# ─────────────────────────────────────────────────────────────
def build_pdf(rd, output_path="amspec_lab_report.pdf"):
    def on_page(c, doc):
        pn = c.getPageNumber()
        draw_header(c, rd, pn)
        draw_footer(c, rd)

    doc = SimpleDocTemplate(
        output_path,
        pagesize=A4,
        topMargin=MT + 55,      # reserve space for drawn header
        bottomMargin=MB + 10,
        leftMargin=ML,
        rightMargin=MR,
    )
    doc.build(build_story(rd), onFirstPage=on_page, onLaterPages=on_page)
    print(f"PDF written to: {output_path}")


# ─────────────────────────────────────────────────────────────
# REPORT DATA
# ─────────────────────────────────────────────────────────────
report_data = {
    "certificate_no":      "AGR 696-26-00027",
    "page_total":          5,

    # Client info
    "attention":           "Ms. Lincy Ann Thomas",
    "client":              "Katrina Factory FZE",
    "address":             "N403 - Mena Jebel Ali - Jebel Ali Freezone \u2013 Dubai, United Arab Emirates",

    # Report meta
    "report_no":           "AAFLC-696-26-00027-001",
    "sample_no":           "696-26-00027-001",
    "date_received":       "27/04/2026",
    "date_reported":       "22/05/2026",
    "container_type":      "Sealed Bag",
    "sample_description":  "Wheat Flour",
    "weight":              "3 Kg",
    "sampled_by":          "Client",
    "sample_temperature":  "(23 to 25) \u00b0C",
    "sample_preservation": "Ambient Temperature",
    "production_date":     "March 2026",
    "expiry_date":         "March 2027",
    "lot_no":              "T55",
    "no_of_pages":         "05",
    "date_of_analysis":    "27/04/2026-18/05/2026",

    # Analyses
    "analyses": [
        {"name": "*Moisture",      "result": "12.69 \u00b1 0.13",  "unit": "%",     "lod": "-", "mu": "1",    "method": "TS EN ISO 712"},
        {"name": "*Ash",           "result": "0.51 \u00b1 0.05",   "unit": "%",     "lod": "-", "mu": "10",   "method": "GMMAY 88"},
        {"name": "*Protein",       "result": "10.32 \u00b1 0.52",  "unit": "%",     "lod": "-", "mu": "5",    "method": "TS 1620"},
        {"name": "Dry Gluten",     "result": "11.85 \u00b1 0.47",  "unit": "%",     "lod": "-", "mu": "4",    "method": "TS EN ISO 21415-3"},
        {"name": "Wet Gluten",     "result": "32.48 \u00b1 0.9744","unit": "%",     "lod": "-", "mu": "3",    "method": "TS EN ISO 21415-1"},
        {"name": "*Falling Number","result": "308",                  "unit": "sn",    "lod": "-", "mu": "20",   "method": "TS EN ISO 3093"},

        {"name": "Granulometry", "type": "section_header"},

        {"name": "Over 6.0 mm Sieve",     "result": "0.0", "unit": "%", "lod": "-", "mu": "-", "method": "TS 4500"},
        {"name": "Over 5.0 mm Sieve",     "result": "0.0", "unit": "%", "lod": "-", "mu": "-", "method": "TS 4500"},
        {"name": "Over 4.0 mm Sieve",     "result": "0.0", "unit": "%", "lod": "-", "mu": "-", "method": "TS 4500"},
        {"name": "Over 2.0 mm Sieve",     "result": "0.0", "unit": "%", "lod": "-", "mu": "-", "method": "TS 4500"},
        {"name": "Between 500-1000 \u03bcm","result": "0.1", "unit": "%", "lod": "-", "mu": "-", "method": "TS 4500"},
        {"name": "Over 800 \u03bcm Sieve","result": "0.0", "unit": "%", "lod": "-", "mu": "-", "method": "TS 4500"},
        {"name": "Over 500 \u03bcm Sieve","result": "0.2", "unit": "%", "lod": "-", "mu": "-", "method": "TS 4500"},
        {"name": "Over 355 \u03bcm Sieve","result": "0.5", "unit": "%", "lod": "-", "mu": "-", "method": "TS 4500"},
        {"name": "Over 212 \u03bcm Sieve","result": "6.2", "unit": "%", "lod": "-", "mu": "-", "method": "TS 4500"},
        {"name": "Under 200 \u03bcm (Fine)","result": "93","unit": "%", "lod": "-", "mu": "-", "method": "TS 4500"},
        {"name": "*Moisture",             "result": "12.70","unit": "%","lod": "-", "mu": "0.18","method": "TS EN ISO 712"},

        {"name": "*Alveograph Analyses",  "result": "Attached (Page no. 3)", "unit": "-", "lod": "-", "mu": "-", "method": "TS EN ISO 27971"},
        {"name": "*P",                    "result": "66",   "unit": "mmH20","lod": "-", "mu": "5",    "method": "TS EN ISO 27971"},
        {"name": "*L",                    "result": "111",  "unit": "mm",   "lod": "-", "mu": "14",   "method": "TS EN ISO 27971"},
        {"name": "*W",                    "result": "182",  "unit": "10E-4J","lod": "-","mu": "13.5", "method": "TS EN ISO 27971"},
        {"name": "*P/L",                  "result": "0.59", "unit": "-",    "lod": "-", "mu": "0.1",  "method": "TS EN ISO 27971"},

        {"name": "*Extensograph Analyses",        "result": "Attached (Page no. 4)", "unit": "-", "lod": "-", "mu": "-", "method": "TS EN ISO 5530-2"},
        {"name": "*Growth Capacity (135 min)",    "result": "Dough did not form",     "unit": "-", "lod": "-", "mu": "-", "method": "TS EN ISO 5530-2"},
        {"name": "*Energy (135 min)",             "result": "Dough did not form",     "unit": "-", "lod": "-", "mu": "-", "method": "TS EN ISO 5530-2"},
        {"name": "*Maximum Resistance (138 min)", "result": "Dough did not form",     "unit": "-", "lod": "-", "mu": "-", "method": "TS EN ISO 5530-2"},

        {"name": "*Farinograph Analyses", "result": "Attached (Page no. 5)", "unit": "-", "lod": "-", "mu": "-", "method": "TS EN ISO 5530-1"},
        {"name": "*Water Lift",           "result": "64.2", "unit": "%",  "lod": "-", "mu": "0.7", "method": "TS EN ISO 5530-1"},
        {"name": "*Development Time",     "result": "2.7",  "unit": "min","lod": "-", "mu": "0.6", "method": "TS EN ISO 5530-1"},
        {"name": "*Stability",            "result": "3.6",  "unit": "min","lod": "-", "mu": "0.6", "method": "TS EN ISO 5530-1"},
        {"name": "*Degree of Softening",  "result": "84",   "unit": "FU", "lod": "-", "mu": "9.2", "method": "TS EN ISO 5530-1"},
    ],

    # Page 2
    "analysis_conducted_by":  "Subcontract Lab",
    "test_method_deviation":  "None",
    "signatory_name":         "Mohammad Ziya Zaidi",
    "signatory_title":        "Technical Manager | UAE",
    "signatory_company":      "AMSPEC MIDDLE EAST",
    "company_name":           "AmSpec Inspection &amp; Testing Services",
    "issued_date":            "22.05.2026",
    "company_tag":            "AMSPEC FIRST CLASS SUPERINTENDENT COMPANY",
}


if __name__ == "__main__":
    output = os.path.join(os.path.dirname(__file__), "amspec_lab_report.pdf")
    build_pdf(report_data, output)
