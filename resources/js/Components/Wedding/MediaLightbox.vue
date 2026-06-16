<script setup>
import { computed, watch } from 'vue';

const props = defineProps({
    media: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

const fileUrl = computed(() => props.media ? `/storage/${props.media.disk_path}` : '#');
const downloadUrl = computed(() => props.media ? `/wedding/media/${props.media.id}/download` : '#');
const isImage = computed(() => props.media?.type === 'image');
const uploadedDate = computed(() => {
    const rawDate = props.media?.uploaded_at || props.media?.created_at;

    return rawDate
        ? new Intl.DateTimeFormat('ru-RU', { dateStyle: 'long', timeStyle: 'short' }).format(new Date(rawDate))
        : 'Дата не указана';
});

watch(
    () => props.media,
    (media) => {
        document.body.classList.toggle('overflow-hidden', Boolean(media));
    },
);
</script>

<template>
    <teleport to="body">
        <div v-if="media" class="fixed inset-0 z-50 flex items-center justify-center bg-stone-950/80 p-4" @click.self="emit('close')">
            <div class="max-h-[92vh] w-full max-w-5xl overflow-hidden rounded-3xl bg-white shadow-2xl">
                <div class="flex items-center justify-between gap-4 border-b border-stone-100 p-4 sm:p-5">
                    <div>
                        <h2 class="text-lg font-bold text-stone-900">Материал от {{ media.guest_name }}</h2>
                        <p class="text-sm text-stone-500">{{ uploadedDate }}</p>
                    </div>
                    <button type="button" class="rounded-full bg-stone-100 px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-200" @click="emit('close')">
                        Закрыть
                    </button>
                </div>

                <div class="bg-stone-950 p-4 sm:p-6">
                    <img
                        v-if="isImage"
                        :src="fileUrl"
                        :alt="`Фото от ${media.guest_name}`"
                        class="mx-auto max-h-[70vh] rounded-2xl object-contain"
                    />
                    <div v-else class="mx-auto flex max-w-xl flex-col items-center gap-5 rounded-2xl bg-white p-8 text-center">
                        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-rose-100 text-4xl text-rose-600">▶</div>
                        <div>
                            <h3 class="text-xl font-bold text-stone-900">Видео доступно по ссылке</h3>
                            <p class="mt-2 text-sm text-stone-600">Откройте видео в новой вкладке или скачайте файл на устройство.</p>
                        </div>
                        <div class="flex flex-wrap justify-center gap-3">
                            <a :href="fileUrl" target="_blank" rel="noopener" class="rounded-full bg-rose-500 px-5 py-3 text-sm font-semibold text-white hover:bg-rose-600">
                                Смотреть видео
                            </a>
                            <a :href="downloadUrl" class="rounded-full border border-rose-200 px-5 py-3 text-sm font-semibold text-rose-600 hover:bg-rose-50">
                                Скачать видео
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </teleport>
</template>
