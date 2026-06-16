<script setup>
import { computed } from 'vue';

const props = defineProps({
    media: {
        type: Object,
        required: true,
    },
});

const emit = defineEmits(['open']);

const fileUrl = computed(() => `/storage/${props.media.disk_path}`);
const thumbnailUrl = computed(() => props.media.thumbnail_path ? `/storage/${props.media.thumbnail_path}` : fileUrl.value);
const uploadedDate = computed(() => {
    const rawDate = props.media.uploaded_at || props.media.created_at;

    return rawDate
        ? new Intl.DateTimeFormat('ru-RU', { dateStyle: 'medium', timeStyle: 'short' }).format(new Date(rawDate))
        : 'Дата не указана';
});
const isImage = computed(() => props.media.type === 'image');
</script>

<template>
    <article class="overflow-hidden rounded-3xl border border-primary-100 bg-white shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
        <button type="button" class="block w-full text-left" @click="emit('open', media)">
            <div class="aspect-square bg-stone-100">
                <img
                    v-if="isImage"
                    :src="thumbnailUrl"
                    :alt="`Фото от ${media.guest_name}`"
                    class="h-full w-full object-cover"
                    loading="lazy"
                />
                <div v-else class="flex h-full w-full flex-col items-center justify-center gap-3 bg-stone-900 text-white">
                    <span class="flex h-16 w-16 items-center justify-center rounded-full bg-white/15 text-3xl">▶</span>
                    <span class="text-sm font-semibold uppercase tracking-[0.25em] text-white/80">Видео</span>
                </div>
            </div>
            <div class="space-y-1 p-4">
                <h3 class="truncate font-semibold text-stone-900">{{ media.guest_name }}</h3>
                <p class="text-sm text-stone-500">{{ uploadedDate }}</p>
            </div>
        </button>
    </article>
</template>
