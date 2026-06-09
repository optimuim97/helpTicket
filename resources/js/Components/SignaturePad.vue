<script setup>
import { ref, onMounted } from 'vue';

const emit = defineEmits(['update:modelValue']);
defineProps({ modelValue: { type: String, default: null } });

const canvas  = ref(null);
let ctx       = null;
let drawing   = false;
let lastX     = 0;
let lastY     = 0;
let hasStrokes = false;

onMounted(() => {
    ctx = canvas.value.getContext('2d');
    ctx.strokeStyle = '#1e3a5f';
    ctx.lineWidth   = 2;
    ctx.lineCap     = 'round';
    ctx.lineJoin    = 'round';
});

function getPos(e) {
    const rect = canvas.value.getBoundingClientRect();
    const src  = e.touches ? e.touches[0] : e;
    return {
        x: (src.clientX - rect.left) * (canvas.value.width  / rect.width),
        y: (src.clientY - rect.top)  * (canvas.value.height / rect.height),
    };
}

function startDraw(e) {
    e.preventDefault();
    drawing = true;
    const p = getPos(e);
    [lastX, lastY] = [p.x, p.y];
    ctx.beginPath();
    ctx.moveTo(lastX, lastY);
}

function draw(e) {
    if (!drawing) return;
    e.preventDefault();
    const p = getPos(e);
    ctx.lineTo(p.x, p.y);
    ctx.stroke();
    [lastX, lastY] = [p.x, p.y];
    hasStrokes = true;
}

function endDraw() {
    if (!drawing) return;
    drawing = false;
    if (hasStrokes) {
        emit('update:modelValue', canvas.value.toDataURL('image/png'));
    }
}

function clear() {
    ctx.clearRect(0, 0, canvas.value.width, canvas.value.height);
    hasStrokes = false;
    emit('update:modelValue', null);
}
</script>

<template>
    <div class="signature-pad-wrapper">
        <div class="relative rounded border border-dashed border-gray-400 bg-gray-50">
            <canvas
                ref="canvas"
                width="400"
                height="150"
                class="block w-full touch-none cursor-crosshair rounded"
                style="max-height: 150px;"
                @mousedown="startDraw"
                @mousemove="draw"
                @mouseup="endDraw"
                @mouseleave="endDraw"
                @touchstart="startDraw"
                @touchmove="draw"
                @touchend="endDraw"
            ></canvas>
            <span class="pointer-events-none absolute bottom-2 left-1/2 -translate-x-1/2 text-xs text-gray-400 select-none">
                Signez ici
            </span>
        </div>
        <button
            type="button"
            @click="clear"
            class="mt-1 text-xs text-red-500 hover:text-red-700"
        >
            Effacer
        </button>
    </div>
</template>
