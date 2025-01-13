<template>
    <div class="flex items-center justify-between">
        <select
            v-model="selectedAIModel"
            class="rounded-md bg-transparent border-none focus:outline-none focus:ring-0"
        >
            <option v-for="model in models" :key="model.name" :value="model.id">
                {{ model.name }}
            </option>
        </select>
        <!-- USER AVATAR  -->
        <div
            class="rounded-full text-white font-extrabold bg-cyan-600 w-8 h-8 flex items-center justify-center"
        >
            {{ user.name.charAt(0).toUpperCase() }}
        </div>
    </div>
</template>

<script setup>
defineProps({
    models: Array,
    user: Object,
});
const selectedAIModel = defineModel();
</script>
