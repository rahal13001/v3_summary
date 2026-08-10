<x-dynamic-component :component="$getFieldWrapperView()" :field="$field">
    @once
        <style>
            .signature-pad {
                display: grid;
                gap: 0.75rem;
            }

            .signature-pad__surface {
                min-height: 15rem;
                overflow: hidden;
                border: 1px solid #d1d5db;
                border-radius: 0.75rem;
                background: #fff;
            }

            .signature-pad__canvas {
                display: block;
                width: 100%;
                min-height: 15rem;
                height: 15rem;
                cursor: crosshair;
                touch-action: none;
                user-select: none;
            }

            .signature-pad__footer {
                display: flex;
                flex-wrap: wrap;
                align-items: center;
                justify-content: space-between;
                gap: 0.75rem;
            }

            .signature-pad__hint {
                margin: 0;
                color: #6b7280;
                font-size: 0.875rem;
                line-height: 1.25rem;
            }

            .signature-pad__actions {
                display: flex;
                flex-wrap: wrap;
                gap: 0.5rem;
            }

            .dark .signature-pad__surface {
                border-color: rgb(255 255 255 / 0.12);
                background: #111827;
            }

            .dark .signature-pad__hint {
                color: #9ca3af;
            }
        </style>
    @endonce

    <div
        wire:ignore
        x-data="{
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }},
            drawing: false,
            hasInk: false,
            lastPoint: null,
            history: [],
            resizeObserver: null,
            renderedState: null,

            init() {
                this.$nextTick(() => {
                    this.render(this.state);
                    this.resizeObserver = new ResizeObserver(() => this.render(this.renderedState));
                    this.resizeObserver.observe(this.$refs.surface);
                });

                this.$watch('state', (value) => {
                    if (value !== this.renderedState) {
                        this.render(value);
                    }
                });
            },

            destroy() {
                this.resizeObserver?.disconnect();
            },

            canvasMetrics() {
                const element = this.$refs.canvas;
                const rect = element.getBoundingClientRect();

                if (! rect.width || ! rect.height) {
                    return null;
                }

                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                const context = element.getContext('2d');
                context.setTransform(ratio, 0, 0, ratio, 0, 0);
                context.lineCap = 'round';
                context.lineJoin = 'round';
                context.strokeStyle = '#111827';
                context.lineWidth = 2.5;

                return { element, context, width: rect.width, height: rect.height, ratio };
            },

            resizeCanvas() {
                const canvas = this.canvasMetrics();

                if (! canvas) {
                    return null;
                }

                const width = Math.round(canvas.width * canvas.ratio);
                const height = Math.round(canvas.height * canvas.ratio);

                if (canvas.element.width !== width || canvas.element.height !== height) {
                    canvas.element.width = width;
                    canvas.element.height = height;
                    canvas.context.setTransform(canvas.ratio, 0, 0, canvas.ratio, 0, 0);
                    canvas.context.lineCap = 'round';
                    canvas.context.lineJoin = 'round';
                    canvas.context.strokeStyle = '#111827';
                    canvas.context.lineWidth = 2.5;
                }

                return canvas;
            },

            render(image = null) {
                const canvas = this.resizeCanvas();

                if (! canvas) {
                    return;
                }

                canvas.context.clearRect(0, 0, canvas.width, canvas.height);
                this.renderedState = image || null;
                this.hasInk = Boolean(image);

                if (! image) {
                    return;
                }

                const source = new Image();
                source.onload = () => {
                    if (this.renderedState !== image) {
                        return;
                    }

                    const currentCanvas = this.resizeCanvas();

                    if (! currentCanvas) {
                        return;
                    }

                    currentCanvas.context.clearRect(0, 0, currentCanvas.width, currentCanvas.height);
                    currentCanvas.context.drawImage(source, 0, 0, currentCanvas.width, currentCanvas.height);
                };
                source.src = image;
            },

            point(event) {
                const rect = this.$refs.canvas.getBoundingClientRect();

                return {
                    x: Math.min(Math.max(event.clientX - rect.left, 0), rect.width),
                    y: Math.min(Math.max(event.clientY - rect.top, 0), rect.height),
                };
            },

            drawDot(point) {
                const context = this.$refs.canvas.getContext('2d');
                context.beginPath();
                context.arc(point.x, point.y, 1.25, 0, Math.PI * 2);
                context.fillStyle = '#111827';
                context.fill();
            },

            snapshot() {
                return this.hasInk ? this.$refs.canvas.toDataURL('image/png') : null;
            },

            start(event) {
                if (event.isPrimary === false || (event.pointerType === 'mouse' && event.button !== 0)) {
                    return;
                }

                event.preventDefault();

                if (! this.canvasMetrics()) {
                    return;
                }

                this.history.push(this.snapshot());
                this.history = this.history.slice(-20);
                this.drawing = true;
                this.lastPoint = this.point(event);
                this.drawDot(this.lastPoint);
                this.hasInk = true;
                this.$refs.canvas.setPointerCapture?.(event.pointerId);
            },

            draw(event) {
                if (! this.drawing) {
                    return;
                }

                event.preventDefault();

                const current = this.point(event);
                const context = this.$refs.canvas.getContext('2d');
                context.beginPath();
                context.moveTo(this.lastPoint.x, this.lastPoint.y);
                context.lineTo(current.x, current.y);
                context.stroke();
                this.lastPoint = current;
            },

            finish(event) {
                if (! this.drawing) {
                    return;
                }

                if (event.type !== 'lostpointercapture') {
                    this.draw(event);
                }

                this.drawing = false;
                this.lastPoint = null;

                if (this.$refs.canvas.hasPointerCapture?.(event.pointerId)) {
                    this.$refs.canvas.releasePointerCapture(event.pointerId);
                }

                this.renderedState = this.$refs.canvas.toDataURL('image/png');
                this.state = this.renderedState;
            },

            clear() {
                if (this.hasInk) {
                    this.history.push(this.snapshot());
                }

                this.render(null);
                this.state = null;
            },

            undo() {
                if (! this.history.length) {
                    return;
                }

                const previous = this.history.pop() || null;
                this.render(previous);
                this.state = previous;
            },
        }"
        class="signature-pad"
    >
        <div x-ref="surface" class="signature-pad__surface">
            <canvas
                x-ref="canvas"
                class="signature-pad__canvas"
                role="img"
                aria-label="Area untuk menggambar tanda tangan"
                aria-describedby="signature-pad-help"
                x-on:pointerdown="start($event)"
                x-on:pointermove="draw($event)"
                x-on:pointerup="finish($event)"
                x-on:pointercancel="finish($event)"
                x-on:lostpointercapture="finish($event)"
            ></canvas>
        </div>

        <div class="signature-pad__footer">
            <p id="signature-pad-help" class="signature-pad__hint" x-text="hasInk ? 'Tanda tangan siap disimpan.' : 'Gambar tanda tangan pada area di atas.'"></p>

            <div class="signature-pad__actions">
                <x-filament::button type="button" color="gray" size="sm" icon="heroicon-m-arrow-uturn-left" x-on:click="undo()" x-bind:disabled="! history.length">
                    Urungkan
                </x-filament::button>
                <x-filament::button type="button" color="danger" size="sm" icon="heroicon-m-trash" x-on:click="clear()" x-bind:disabled="! hasInk">
                    Hapus
                </x-filament::button>
            </div>
        </div>
    </div>
</x-dynamic-component>
