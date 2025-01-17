<template>
    <div class="flex items-center justify-between p-4">
        <div>
            <i
                v-if="!isMenuOpen"
                class="fa-solid fa-table-columns text-xl text-gray-600"
                @click="$emit('update:isMenuOpen', !isMenuOpen)"
            ></i>
            <select
                v-model="selectedAIModel"
                class="rounded-md bg-transparent border-none focus:outline-none focus:ring-0"
            >
                <option
                    v-for="model in models"
                    :key="model.name"
                    :value="model.id"
                >
                    {{ model.name }}
                </option>
            </select>
        </div>
    </div>
</template>

<script setup>
defineProps({
    models: Array,
    user: Object,
    isMenuOpen: Boolean,
});
const selectedAIModel = defineModel();

defineEmits(["update:isMenuOpen"]);
</script>
