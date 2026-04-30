<script lang="ts">
    import { cn } from '@/lib/utils';

    let { class: className = '', type = 'text', value = $bindable(), ...rest } = $props();
</script>

<input
    type={type}
    class={cn(
        'flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm text-black dark:text-white shadow-sm transition-colors file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-gray-500 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
        className,
    )}
    bind:value
    {...rest}
/>
