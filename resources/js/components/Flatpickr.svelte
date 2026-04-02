<script lang="ts">
    import { onMount, onDestroy } from 'svelte';
    import flatpickr from 'flatpickr';
    import 'flatpickr/dist/flatpickr.css';

    let {
        value = $bindable(''),
        options = {},
        placeholder = '',
        class: className = '',
        id = '',
    } = $props();

    let inputElement: HTMLInputElement;
    let instance: flatpickr.Instance;

    onMount(() => {
        instance = flatpickr(inputElement, {
            ...options,
            defaultDate: value,
            onChange: (selectedDates, dateStr) => {
                value = dateStr;
            },
        });
    });

    // Update flatpickr if value changes externally
    $effect(() => {
        if (instance && value !== instance.input.value) {
            instance.setDate(value, false);
        }
    });

    onDestroy(() => {
        if (instance) instance.destroy();
    });
</script>

<input
    bind:this={inputElement}
    {id}
    class="form-control {className}"
    {placeholder}
    readonly
/>

<style>
    /* Customization to make it look more premium */
    :global(.flatpickr-calendar) {
        border: 0 !important;
        box-shadow:
            0 10px 15px -3px rgba(0, 0, 0, 0.1),
            0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        border-radius: 8px !important;
    }
    :global(.flatpickr-day.selected) {
        background: #0d6efd !important;
        border-color: #0d6efd !important;
    }
</style>
