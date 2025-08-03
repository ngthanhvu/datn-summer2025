<template>
    <div v-if="show" class="modal-overlay">
        <div class="modal" :class="{ 'modal-lg': size === 'lg' }">
            <div class="modal-header">
                <h2>{{ title }}</h2>
                <button @click="$emit('close')" class="close-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="modal-body">
                <slot></slot>
            </div>
            <div class="modal-footer">
                <slot name="footer">
                    <button @click="$emit('close')" class="btn-cancel">Hủy</button>
                    <button @click="$emit('save')" class="btn-save">Lưu</button>
                </slot>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    show: {
        type: Boolean,
        required: true
    },
    title: {
        type: String,
        required: true
    },
    size: {
        type: String,
        default: 'md'
    }
})

defineEmits(['close', 'save'])
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal {
    background: white;
    border-radius: 8px;
    width: 500px;
    max-width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}

.modal.modal-lg {
    width: 800px;
}

.modal-header {
    padding: 1.5rem;
    border-bottom: 1px solid #e5e7eb;
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: sticky;
    top: 0;
    background: white;
    z-index: 1;
}

.modal-header h2 {
    font-size: 1.25rem;
    font-weight: 600;
    color: #111827;
}

.close-btn {
    background: none;
    border: none;
    font-size: 1.25rem;
    color: #6b7280;
    cursor: pointer;
}

.modal-body {
    padding: 1.5rem;
}

.modal-footer {
    padding: 1.5rem;
    border-top: 1px solid #e5e7eb;
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
}

.btn-cancel,
.btn-save {
    padding: 0.5rem 1rem;
    border-radius: 6px;
    font-weight: 500;
    cursor: pointer;
}

.btn-cancel {
    background: white;
    border: 1px solid #e5e7eb;
    color: #374151;
}

.btn-save {
    background: #3bb77e;
    border: none;
    color: white;
}

.btn-save:hover {
    background: #2ea16d;
}
</style>