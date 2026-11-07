{{-- Camera barcode scanner modal (New Task 1) --}}
<div class="modal fade" id="camera_scanner_modal" tabindex="-1" role="dialog" aria-labelledby="cameraScannerModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="cameraScannerModalLabel">Scan barcode with camera</h4>
            </div>
            <div class="modal-body">
                <div id="camera_scanner_reader" style="width:100%;min-height:300px;background:#000;"></div>
                <p id="camera_scanner_status" class="text-muted" style="margin-top:8px;">Point your camera at the barcode or QR.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Inline script so it renders regardless of @section cascade behavior inside @include --}}
<script src="{{ asset('js/vendor/html5-qrcode.min.js') }}"></script>
<script>
    (function () {
        var scanner = null;
        var lastResult = '';
        var lastAt = 0;

        function playBeep() {
            try {
                var a = document.getElementById('success-audio');
                if (a) {
                    a.currentTime = 0;
                    var p = a.play();
                    if (p && typeof p.catch === 'function') { p.catch(function () {}); }
                }
            } catch (e) {}
        }

        function setStatus(msg) {
            var s = document.getElementById('camera_scanner_status');
            if (s) s.innerText = msg;
        }

        function stopScanner(cb) {
            if (scanner && scanner.getState && scanner.getState() === 2) {
                scanner.stop().then(function () {
                    try { scanner.clear(); } catch (e) {}
                    scanner = null;
                    if (cb) cb();
                }).catch(function () { scanner = null; if (cb) cb(); });
            } else {
                scanner = null;
                if (cb) cb();
            }
        }

        function onDecoded(decodedText) {
            var now = Date.now();
            if (decodedText === lastResult && (now - lastAt) < 1500) return;
            lastResult = decodedText;
            lastAt = now;

            playBeep();

            var search = document.getElementById('search_product');
            if (search) {
                search.value = decodedText;
                var evt;
                try { evt = new Event('keyup', { bubbles: true }); }
                catch (e) { evt = document.createEvent('Event'); evt.initEvent('keyup', true, true); }
                search.dispatchEvent(evt);
                if (window.jQuery) { jQuery(search).trigger('change').trigger('keyup'); }
            }
            setStatus('Scanned: ' + decodedText);
            stopScanner(function () {
                var $m = window.jQuery ? window.jQuery('#camera_scanner_modal') : null;
                if ($m) $m.modal('hide');
            });
        }

        function buildConfig() {
            // Wide rectangular scan box tuned for 1D product barcodes (EAN/UPC/CODE128)
            // while still large enough to catch a QR tile when the cashier presents one.
            var w = (window.jQuery && jQuery(window).width()) ? jQuery(window).width() : window.innerWidth;
            var boxW = 320, boxH = 140;
            if (w <= 400) { boxW = 260; boxH = 110; }
            if (w <= 320) { boxW = 200; boxH = 90; }
            var cfg = { fps: 25, qrbox: { width: boxW, height: boxH } };
            // Prefer 1D barcode formats first (common on retail products), then QR as fallback.
            if (typeof Html5QrcodeSupportedFormats !== 'undefined') {
                cfg.formatsToSupport = [
                    Html5QrcodeSupportedFormats.EAN_13,
                    Html5QrcodeSupportedFormats.EAN_8,
                    Html5QrcodeSupportedFormats.UPC_A,
                    Html5QrcodeSupportedFormats.UPC_E,
                    Html5QrcodeSupportedFormats.CODE_128,
                    Html5QrcodeSupportedFormats.CODE_39,
                    Html5QrcodeSupportedFormats.ITF,
                    Html5QrcodeSupportedFormats.CODABAR,
                    Html5QrcodeSupportedFormats.QR_CODE,
                ];
            }
            return cfg;
        }

        function startScanner() {
            var reader = document.getElementById('camera_scanner_reader');
            if (!reader) return;
            reader.innerHTML = '';
            if (typeof Html5Qrcode === 'undefined') {
                setStatus('Scanner library failed to load.');
                return;
            }
            try { scanner = new Html5Qrcode('camera_scanner_reader'); }
            catch (e) {
                setStatus('Scanner init error: ' + (e && e.message ? e.message : e));
                return;
            }
            setStatus('Starting back camera...');
            scanner.start({ facingMode: 'environment' }, buildConfig(), onDecoded, function (_err) {})
                .then(function () {
                    setStatus('Point your camera at the barcode or QR.');
                })
                .catch(function (err) {
                    var msg = (err && err.message) ? err.message : String(err);
                    if (location.protocol !== 'https:' && location.hostname !== 'localhost') {
                        setStatus('Camera requires HTTPS. ' + msg);
                    } else if (/Permission|NotAllowed/i.test(msg)) {
                        setStatus('Camera permission denied. Please allow camera access in your browser settings.');
                    } else if (/NotFound|OverconstrainedError/i.test(msg)) {
                        setStatus('Back camera not found on this device.');
                    } else if (/NotReadable|TrackStart/i.test(msg)) {
                        setStatus('Camera is in use by another app or tab. Close other apps using the camera and retry.');
                    } else {
                        setStatus('Camera error: ' + msg);
                    }
                });
        }

        function bindEvents() {
            if (!window.jQuery) {
                setTimeout(bindEvents, 200);
                return;
            }
            jQuery(document).on('shown.bs.modal', '#camera_scanner_modal', function () {
                startScanner();
            });
            jQuery(document).on('hidden.bs.modal', '#camera_scanner_modal', function () {
                stopScanner();
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', bindEvents);
        } else {
            bindEvents();
        }
    })();
</script>
