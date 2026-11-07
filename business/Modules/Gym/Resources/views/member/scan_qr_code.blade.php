@extends('gym::layouts.scanner')
@section('title', __('gym::lang.id_card_scanner'))
@section('content')
    <div class="row">
        <div class="col-md-4"></div>
        <div class="col-md-4">
            <div
                class="tw-p-5 md:tw-p-6 tw-mb-4 tw-rounded-2xl tw-transition-all tw-duration-200 tw-bg-white tw-shadow-sm tw-ring-1 tw-ring-gray-200">
                <div class="tw-flex tw-flex-col tw-gap-4 tw-dw-rounded-box tw-dw-p-6 tw-dw-max-w-md">
                    <div class="tw-flex tw-items-center tw-flex-col">
                        <h1 class="tw-text-lg md:tw-text-xl tw-font-semibold tw-text-[#1e1e1e]">
                            {{ __('gym::lang.scan_qr_code') }}
                        </h1>
                    </div>
                    <div id="reader"></div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
        </div>
        <div class="col-md-12 col-xs-12">
            <div class="tw-rounded-2xl">
                <div class="tw-flex tw-flex-col tw-gap-4 tw-dw-rounded-box tw-dw-p-6 tw-dw-max-w-md repair_status_details">
                </div>
            </div>
        </div>
    </div>

    <style>
        #html5-qrcode-button-camera-start,
        #html5-qrcode-button-camera-stop {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            background: linear-gradient(to right, #4F46E5, #3B82F6);
            color: white;
            font-weight: 600;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.2s;
            width: 90%;
            margin: 5px 0;
        }

        #html5-qrcode-button-camera-start:hover,
        #html5-qrcode-button-camera-stop:hover {
            background: linear-gradient(to right, #4338CA, #2563EB);
        }

        #html5-qrcode-button-camera-stop {
            background: linear-gradient(to right, #EF4444, #DC2626);
        }

        #html5-qrcode-button-camera-stop:hover {
            background: linear-gradient(to right, #DC2626, #B91C1C);
        }
    </style>
@endsection
@section('javascript')
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script type="text/javascript">
        $(document).ready(function() {

            function playBeep() {
                const context = new(window.AudioContext || window.webkitAudioContext)();
                const oscillator = context.createOscillator();
                const gainNode = context.createGain();

                oscillator.type = "sine"; // Can be "sine", "square", "sawtooth", or "triangle"
                oscillator.frequency.setValueAtTime(800, context.currentTime); // Frequency of the beep
                gainNode.gain.setValueAtTime(0.2, context.currentTime); // Volume

                oscillator.connect(gainNode);
                gainNode.connect(context.destination);
                oscillator.start();
                setTimeout(() => {
                    oscillator.stop();
                }, 200); // Beep duration in milliseconds
            }

            function onScanSuccess(decodedText, decodedResult) {
                playBeep();
                setTimeout(() => {
                    html5QrCode.stop().then(() => {
                        $.ajax({
                            url: "{{ route('get_signed_route') }}",
                            method: 'GET',
                            data: {
                                memberId: decodedText
                            },
                            dataType: 'json',
                            success: function(response) {
                                window.location.href = response.signedUrl;
                            },
                            error: function(xhr, status, error) {
                                console.error('Error:', error);
                            }
                        });

                    }).catch(err => console.log("Failed to stop scanning:", err));
                }, 100); // Green success frame remains for 1 second
            }

            function onScanError(errorMessage) {
                // handle scan error
            }

            const html5QrCode = new Html5Qrcode("reader");

            html5QrCode.start({
                    facingMode: "environment"
                }, {
                    fps: 15, // Increase frames per second for faster scanning
                    qrbox: {
                        width: 250,
                        height: 250
                    }, // Smaller scan area for quick detection
                    disableFlip: false, // Allows front camera flipping
                    showTorchButtonIfSupported: true, // Enable torch button for low light
                    formatsToSupport: [Html5QrcodeSupportedFormats.QR_CODE] // Focus only on QR codes
                },
                onScanSuccess,
                onScanError
            ).catch(err => console.log("Camera access error:", err));


        });
    </script>
@endsection
