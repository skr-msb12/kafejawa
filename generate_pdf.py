import os
import shutil
import subprocess
import sys
from pathlib import Path


def find_edge_binary() -> str:
    edge_in_path = shutil.which("msedge")
    if edge_in_path:
        return edge_in_path
    candidates = [
        r"C:\Program Files (x86)\Microsoft\Edge\Application\msedge.exe",
        r"C:\Program Files\Microsoft\Edge\Application\msedge.exe",
        os.path.expandvars(r"%LOCALAPPDATA%\Microsoft\Edge\Application\msedge.exe"),
    ]
    for candidate in candidates:
        if os.path.isfile(candidate):
            return candidate
    raise FileNotFoundError("Microsoft Edge executable not found.")


def generate_pdf() -> None:
    project_root = Path(__file__).resolve().parent
    html_file = project_root / "docs" / "tahap-1-perencanaan-desain.html"
    output_pdf = project_root / "docs" / "TAHAP-1_PERENCANAAN_DESAIN_KAFEJAWA.pdf"

    if not html_file.is_file():
        raise FileNotFoundError(f"HTML source file missing: {html_file}")

    edge_bin = find_edge_binary()

    cmd = [
        edge_bin,
        "--headless",
        "--disable-gpu",
        "--no-pdf-header-footer",
        "--run-all-compositor-stages-before-draw",
        f"--print-to-pdf={output_pdf}",
        str(html_file),
    ]

    result = subprocess.run(cmd, capture_output=True, text=True, check=False)
    if result.returncode != 0:
        raise RuntimeError(f"Edge process exited with code {result.returncode}: {result.stderr}")

    if not output_pdf.is_file():
        raise FileNotFoundError(f"PDF file was not created at expected path: {output_pdf}")

    file_size = output_pdf.stat().st_size
    min_size_bytes = 10 * 1024
    if file_size < min_size_bytes:
        raise ValueError(
            f"Generated PDF file size ({file_size} bytes) is below minimum threshold ({min_size_bytes} bytes)."
        )

    print(f"PDF successfully generated: {output_pdf} ({file_size:,} bytes, >10KB valid)")


if __name__ == "__main__":
    try:
        generate_pdf()
    except Exception as err:
        print(f"Error: {err}", file=sys.stderr)
        sys.exit(1)
