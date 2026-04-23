@php
    $p = $preview ?? [];
    $first = $p['first_name'] ?? '';
    $middle = $p['middle_initial'] ?? '';
    $last = $p['last_name'] ?? '';
    $nameLineFirst = trim(strtoupper($first));
    $middleRaw = trim((string) ($middle ?? ''));
    $nameLineMiddle = $middleRaw !== ''
        ? strtoupper(mb_substr(rtrim($middleRaw, '.'), 0, 1)) . '.'
        : '';
    $nameLineLast = trim(strtoupper($last));
    $nameLineMiddleLast = trim(
        ($nameLineMiddle !== '' ? $nameLineMiddle . ' ' : '') . $nameLineLast
    );
    $designation = $p['designation'] ?? '';
    $office = $p['office_name'] ?? '';
    $idNo = $p['id_number'] ?? '';
    $templateW = (int) ($templateBackgroundWidth ?? 0);
    $templateH = (int) ($templateBackgroundHeight ?? 0);
    $templateRatio = $templateW > 0 && $templateH > 0 ? ($templateW / $templateH) : 1.586;
    $mirrorPrintEnabled = (bool) ($mirrorPrint ?? false);
    $gothamBook = public_path('fonts/Gotham-Book.woff2');
    $gothamMedium = public_path('fonts/Gotham-Medium.woff2');
    $gothamBold = public_path('fonts/Gotham-Bold.woff2');
    $gothamBlack = public_path('fonts/Gotham-Black.woff2');
@endphp

@once
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <script>
        window.__downloadPreviewIdCard = async function (mirror) {
            const source = document.querySelector('#id-print-root .id-template-copy--primary');
            if (!source) {
                alert('Preview card not found.');
                return;
            }

            const ensureHtml2Canvas = async () => {
                if (typeof window.html2canvas !== 'undefined') {
                    return;
                }
                throw new Error('html2canvas is not available.');
            };

            const ensureHtmlToImage = async () => {
                if (typeof window.htmlToImage !== 'undefined') {
                    return;
                }
                throw new Error('html-to-image is not available.');
            };

            try {
                await ensureHtml2Canvas();
            } catch (_error) {
                alert('Image export library is not loaded. Please run npm build/dev and refresh this page.');
                return;
            }

            const target = source.cloneNode(true);
            target.style.margin = '0';
            target.style.boxShadow = 'none';
            target.style.border = 'none';
            target.style.transform = mirror ? 'scaleX(-1)' : 'none';
            target.style.transformOrigin = 'center';

            const blobToDataUrl = (blob) => new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = () => resolve(reader.result);
                reader.onerror = () => reject(new Error('Failed to read image blob.'));
                reader.readAsDataURL(blob);
            });

            const inlineImage = async (img) => {
                const src = img.getAttribute('src') || '';
                if (!src || src.startsWith('data:')) {
                    return;
                }

                try {
                    const response = await fetch(src, {
                        mode: 'cors',
                        credentials: 'include',
                    });
                    if (!response.ok) {
                        return;
                    }
                    const blob = await response.blob();
                    const dataUrl = await blobToDataUrl(blob);
                    if (typeof dataUrl === 'string') {
                        img.setAttribute('src', dataUrl);
                    }
                } catch (_error) {
                    // Keep original src when fetch fails.
                }
            };

            const inlineBackgroundImage = async (el) => {
                const raw = el.style.backgroundImage || '';
                const match = raw.match(/url\((['"]?)(.*?)\1\)/i);
                if (!match || !match[2]) {
                    return;
                }

                const src = match[2];
                if (src.startsWith('data:')) {
                    return;
                }

                try {
                    const response = await fetch(src, {
                        mode: 'cors',
                        credentials: 'include',
                    });
                    if (!response.ok) {
                        el.style.backgroundImage = 'none';
                        return;
                    }
                    const blob = await response.blob();
                    const dataUrl = await blobToDataUrl(blob);
                    if (typeof dataUrl === 'string') {
                        el.style.backgroundImage = `url("${dataUrl}")`;
                    }
                } catch (_error) {
                    el.style.backgroundImage = 'none';
                }
            };

            const waitForImages = async (root) => {
                const imgs = Array.from(root.querySelectorAll('img'));
                await Promise.all(imgs.map(async (img) => {
                    await inlineImage(img);
                    if (img.complete) {
                        return;
                    }
                    await new Promise((resolve) => {
                        img.addEventListener('load', resolve, { once: true });
                        img.addEventListener('error', resolve, { once: true });
                    });
                }));

                const bgNodes = Array.from(root.querySelectorAll('[style*="background-image"]'));
                await Promise.all(bgNodes.map((el) => inlineBackgroundImage(el)));
            };

            await waitForImages(target);

            const sandbox = document.createElement('div');
            sandbox.style.position = 'fixed';
            sandbox.style.left = '-10000px';
            sandbox.style.top = '0';
            sandbox.style.padding = '0';
            sandbox.style.background = '#fff';
            sandbox.appendChild(target);
            document.body.appendChild(sandbox);

            const renderCanvas = async (node, safeMode = false) => {
                if (safeMode) {
                    node.querySelectorAll('[style*="background-image"]').forEach((el) => {
                        el.style.backgroundImage = 'none';
                    });
                    node.querySelectorAll('img').forEach((img) => {
                        if (!img.getAttribute('src')?.startsWith('data:')) {
                            img.removeAttribute('src');
                        }
                    });
                }

                return window.html2canvas(node, {
                    backgroundColor: null,
                    scale: 2,
                    useCORS: true,
                    allowTaint: false,
                    logging: false,
                });
            };

            try {
                const downloadBlob = (blob) => {
                    if (!(blob instanceof Blob) || blob.size === 0) {
                        throw new Error('Empty export blob.');
                    }

                    const link = document.createElement('a');
                    link.download = mirror ? 'id-card-mirror.png' : 'id-card-normal.png';
                    link.href = URL.createObjectURL(blob);
                    document.body.appendChild(link);
                    link.click();
                    link.remove();
                    setTimeout(() => URL.revokeObjectURL(link.href), 1000);
                };

                const canvasToBlob = (canvas) => new Promise((resolve, reject) => {
                    canvas.toBlob((blob) => {
                        if (!blob) {
                            reject(new Error('Failed to create canvas blob.'));
                            return;
                        }
                        resolve(blob);
                    }, 'image/png', 1);
                });

                try {
                    let canvas;
                    try {
                        canvas = await renderCanvas(target, false);
                    } catch (_firstError) {
                        canvas = await renderCanvas(target, true);
                    }
                    const blob = await canvasToBlob(canvas);
                    downloadBlob(blob);
                } catch (_canvasError) {
                    await ensureHtmlToImage();
                    let blob;
                    if (typeof window.htmlToImage.toBlob === 'function') {
                        blob = await window.htmlToImage.toBlob(target, {
                            cacheBust: true,
                            pixelRatio: 2,
                        });
                    } else {
                        const dataUrl = await window.htmlToImage.toPng(target, {
                            cacheBust: true,
                            pixelRatio: 2,
                        });
                        const response = await fetch(dataUrl);
                        blob = await response.blob();
                    }
                    downloadBlob(blob);
                }
            } catch (_error) {
                alert('Could not export preview image. Please refresh and try again.');
            } finally {
                sandbox.remove();
            }
        };

        document.addEventListener('livewire:init', () => {
            Livewire.on('download-preview-id-card', (event) => {
                const payload = Array.isArray(event) ? (event[0] ?? {}) : (event ?? {});
                const mirror = !!payload.mirror;
                window.__downloadPreviewIdCard(mirror);
            });
        });
    </script>
@endonce

<div
    id="id-print-root"
    class="id-card-preview-root mx-auto w-full max-w-sm print:max-w-none {{ $mirrorPrintEnabled ? 'is-mirror-print' : '' }}"
    style="--id-template-ratio: {{ $templateRatio }};"
>
    <style>
        @if(file_exists($gothamBook))
        @font-face {
            font-family: 'Gotham';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: url('{{ asset('fonts/Gotham-Book.woff2') }}') format('woff2');
        }
        @endif
        @if(file_exists($gothamMedium))
        @font-face {
            font-family: 'Gotham';
            font-style: normal;
            font-weight: 500;
            font-display: swap;
            src: url('{{ asset('fonts/Gotham-Medium.woff2') }}') format('woff2');
        }
        @endif
        @if(file_exists($gothamBold))
        @font-face {
            font-family: 'Gotham';
            font-style: normal;
            font-weight: 700;
            font-display: swap;
            src: url('{{ asset('fonts/Gotham-Bold.woff2') }}') format('woff2');
        }
        @endif
        @if(file_exists($gothamBlack))
        @font-face {
            font-family: 'Gotham';
            font-style: normal;
            font-weight: 800;
            font-display: swap;
            src: url('{{ asset('fonts/Gotham-Black.woff2') }}') format('woff2');
        }
        @elseif(file_exists($gothamBold))
        @font-face {
            font-family: 'Gotham';
            font-style: normal;
            font-weight: 800;
            font-display: swap;
            src: url('{{ asset('fonts/Gotham-Bold.woff2') }}') format('woff2');
        }
        @endif
        /* Gotham is proprietary; add WOFF2 files to public/fonts if licensed. Otherwise Montserrat approximates the Gotham look. */
        #id-print-root.id-card-preview-root,
        #id-print-root.id-card-preview-root .id-overlay {
            font-family: 'Gotham', 'Gotham Office', 'Montserrat', 'Segoe UI', Helvetica, Arial, sans-serif;
        }
        .id-template-canvas {
            position: relative;
            width: 100%;
            aspect-ratio: var(--id-template-ratio, 1.586);
            border-radius: 0.75rem;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.08);
            background-color: #0b3f73;
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
        .id-template-image {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
            display: block;
            z-index: 0;
        }
        .id-print-copies {
            width: 100%;
            display: grid;
            grid-template-columns: 1fr;
            justify-items: center;
            gap: 0.85rem;
        }
        .id-template-copy--duplicate {
            display: none;
        }
        .id-overlay { position: absolute; inset: 0; z-index: 1; }
        /*
         * Portrait: orange outer ring + thin white mat + circular clip.
         * Shadow on a wrapper so overflow:hidden on the ring does not clip it.
         */
        .id-overlay-photo-shell {
            position: absolute;
            top: 14%;
            left: 33%;
            width: 33%;
            aspect-ratio: 1 / 1;
            z-index: 4;
            filter: drop-shadow(0 4px 14px rgba(0, 45, 86, 0.22)) drop-shadow(0 2px 4px rgba(0, 0, 0, 0.12));
        }
        .id-overlay-photo {
            position: absolute;
            inset: 0;
            border-radius: 50%;
            overflow: hidden;
            box-sizing: border-box;
            background: #ffffff;
            border: 4px solid #E78D2C;
            padding: 2px;
            isolation: isolate;
        }
        .id-overlay-photo-inner {
            position: absolute;
            inset: 2px;
            border-radius: 50%;
            overflow: hidden;
            background: #ffffff;
        }
        .id-overlay-photo-inner img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            /* Top-weighted crop: hair near top of ring, shoulders in lower curve */
            object-position: 50% 12%;
            display: block;
        }
        .id-overlay-name {
            position: absolute;
            top: 40%;
            left: 10%;
            width: 80%;
            text-align: center;
            font-weight: 400;
            color: #1f4b89;
            line-height: 1.05;
            text-transform: uppercase;
        }
        .id-overlay-name > span {
            display: block;
        }
        .id-overlay-name-line--first {
            font-size: 31.62pt;
            font-weight: 700;
            font-family: 'Myriad Pro', 'MyriadPro', 'MyriadPro-Regular', 'Gotham', 'Montserrat', 'Segoe UI', Arial, sans-serif;
        }
        /* Middle initial + last name on one line, single size */
        .id-overlay-name-line--middle-last {
            display: block;
            font-size: 20pt;
            margin-top: 0.04em;
            font-weight: 700;
            letter-spacing: 0.02em;
            font-family: 'Myriad Pro', 'MyriadPro', 'MyriadPro-Regular', 'Gotham', 'Montserrat', 'Segoe UI', Arial, sans-serif;
        }
        .id-overlay-designation {
            position: absolute;
            top: 50.5%;
            left: 10%;
            width: 80%;
            text-align: center;
            font-size: 9pt;
            font-weight: 700;
            color: #111;
            line-height: 1.1;
            text-transform: uppercase;
        }
        .id-overlay-office {
            position: absolute;
            top: 53%;
            left: 10%;
            width: 80%;
            text-align: center;
            font-size: 20pt;
            font-weight: 800;
            color: #f57900;
            line-height: 1.05;
            text-transform: uppercase;
        }
        .id-overlay-id {
            position: absolute;
            bottom: 3.1%;
            left: 39%;
            transform: translateX(-20%);
            font-size: 9pt;
            font-weight: 600;
            color: #fff;
            text-transform: uppercase;
        }
        .id-overlay-qr {
            position: absolute;
            right: 6.5%;
            bottom: 3.9%;
            width: 18.5%;
            aspect-ratio: 1 / 1;
            overflow: hidden;
        }
        .id-overlay-qr img { width: 100%; height: 100%; object-fit: contain; }
        @media print {
            @page { margin: 0; size: auto; }
            .fi-sidebar, .fi-topbar, .fi-header, .fi-sidebar-close-overlay { display: none !important; }
            .fi-main { padding: 0 !important; }
            body * { visibility: hidden; }
            #id-print-root, #id-print-root * { visibility: visible; }
            #id-print-root.id-card-preview-root,
            #id-print-root.id-card-preview-root * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
            }
            #id-print-root {
                position: absolute;
                left: 50%;
                top: 0.35in;
                transform: translateX(-50%);
                width: min(11in, 100vw);
                max-width: 11in;
            }
            .id-print-copies {
                grid-template-columns: repeat(2, minmax(0, 1fr));
                align-items: start;
                justify-items: center;
                column-gap: 0.25in;
                row-gap: 0;
            }
            .id-template-copy--duplicate {
                display: block;
            }
            .id-template-canvas {
                width: 100%;
                max-width: 5in;
            }
            #id-print-root.is-mirror-print .id-template-canvas {
                transform: scaleX(-1);
                transform-origin: center;
            }
        }
    </style>

    @if(! empty($templateBackgroundUrl))
        <div class="id-print-copies">
            <div class="id-template-canvas id-template-copy--primary" style="background-image: url('{{ $templateBackgroundUrl }}');">
                <img class="id-template-image" src="{{ $templateBackgroundUrl }}" alt="ID Template">
                <div class="id-overlay">
                    <div class="id-overlay-photo-shell">
                        <div class="id-overlay-photo">
                            @if(! empty($profileUrl))
                                <div class="id-overlay-photo-inner">
                                    <img src="{{ $profileUrl }}" alt="Profile">
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="id-overlay-name">
                        <span class="id-overlay-name-line--first">{{ $nameLineFirst !== '' ? $nameLineFirst : 'FIRST NAME' }}</span>
                        <span class="id-overlay-name-line--middle-last">{{ $nameLineMiddleLast !== '' ? $nameLineMiddleLast : 'M. LAST NAME' }}</span>
                    </div>
                    <div class="id-overlay-designation">{{ $designation !== '' ? $designation : 'DESIGNATION' }}</div>
                    <div class="id-overlay-office">{{ $office !== '' ? $office : 'OFFICE NAME' }}</div>
                    <div class="id-overlay-id">{{ $idNo !== '' ? 'ID NO: ' . $idNo : 'ID NO: ----' }}</div>
                    <div class="id-overlay-qr">
                        @if(! empty($qrDataUri))
                            <img src="{{ $qrDataUri }}" alt="QR">
                        @endif
                    </div>
                </div>
            </div>

            <div class="id-template-canvas id-template-copy--duplicate" style="background-image: url('{{ $templateBackgroundUrl }}');" aria-hidden="true">
                <img class="id-template-image" src="{{ $templateBackgroundUrl }}" alt="">
                <div class="id-overlay">
                    <div class="id-overlay-photo-shell">
                        <div class="id-overlay-photo">
                            @if(! empty($profileUrl))
                                <div class="id-overlay-photo-inner">
                                    <img src="{{ $profileUrl }}" alt="">
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="id-overlay-name">
                        <span class="id-overlay-name-line--first">{{ $nameLineFirst !== '' ? $nameLineFirst : 'FIRST NAME' }}</span>
                        <span class="id-overlay-name-line--middle-last">{{ $nameLineMiddleLast !== '' ? $nameLineMiddleLast : 'M. LAST NAME' }}</span>
                    </div>
                    <div class="id-overlay-designation">{{ $designation !== '' ? $designation : 'DESIGNATION' }}</div>
                    <div class="id-overlay-office">{{ $office !== '' ? $office : 'OFFICE NAME' }}</div>
                    <div class="id-overlay-id">{{ $idNo !== '' ? 'ID NO: ' . $idNo : 'ID NO: ----' }}</div>
                    <div class="id-overlay-qr">
                        @if(! empty($qrDataUri))
                            <img src="{{ $qrDataUri }}" alt="">
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="rounded-xl border border-gray-200 p-4 text-sm text-gray-600 dark:border-gray-700 dark:text-gray-300">
            Upload a preview template image in Settings -> ID card template to show your exact design here.
        </div>
    @endif
</div>
