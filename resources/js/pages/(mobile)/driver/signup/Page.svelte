<script module lang="ts">
    export const layout = {
        title: 'Driver Registration',
        description: 'Join Siwride as a partner driver',
    };
</script>

<script lang="ts">
    import { useForm } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import PasswordInput from '@/components/PasswordInput.svelte';
    import TextLink from '@/components/TextLink.svelte';
    import { Button } from '@/components/ui/button';
    import { Input } from '@/components/ui/input';
    import { Label } from '@/components/ui/label';
    import { Spinner } from '@/components/ui/spinner';
    import { login } from '@/routes';

    let form = useForm({
        name: '',
        email: '',
        phone: '',
        password: '',
        password_confirmation: '',
        vehicle_type: '',
        vehicle_registration_number: '',
    });

    function submit() {
        form.post('/driver/register', {
            onFinish: () => form.reset('password', 'password_confirmation'),
        });
    }
</script>

<AppHead title="Driver Register" />

<div class="flex flex-col gap-6">
    <div class="flex flex-col gap-2">
        <h1 class="text-2xl font-bold">Driver Partner Registration</h1>
        <p class="text-sm text-muted-foreground">
            Fill in your details and vehicle information to apply.
        </p>
    </div>

    <form onsubmit={submit} class="grid gap-6">
        <div class="grid gap-2">
            <Label for="name">Full Name</Label>
            <Input
                id="name"
                type="text"
                required
                bind:value={form.name}
                placeholder="Full name"
            />
            <InputError message={form.errors.name} />
        </div>

        <div class="grid gap-2">
            <Label for="email">Email address</Label>
            <Input
                id="email"
                type="email"
                required
                bind:value={form.email}
                placeholder="email@example.com"
            />
            <InputError message={form.errors.email} />
        </div>

        <div class="grid gap-2">
            <Label for="phone">Phone Number</Label>
            <Input
                id="phone"
                type="tel"
                required
                bind:value={form.phone}
                placeholder="e.g. +628123456789"
            />
            <InputError message={form.errors.phone} />
        </div>

        <div class="grid gap-2">
            <Label for="vehicle_type">Vehicle Type</Label>
            <select
                id="vehicle_type"
                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50"
                required
                bind:value={form.vehicle_type}
            >
                <option value="">Select vehicle</option>
                <option value="Car (Economy)">Car (Economy)</option>
                <option value="Car (Comfort)">Car (Comfort)</option>
                <option value="Car (Business)">Car (Business)</option>
                <option value="Motorcycle">Motorcycle</option>
            </select>
            <InputError message={form.errors.vehicle_type} />
        </div>

        <div class="grid gap-2">
            <Label for="vehicle_registration_number"
                >Vehicle Registration Number (Plat Nomor)</Label
            >
            <Input
                id="vehicle_registration_number"
                type="text"
                required
                bind:value={form.vehicle_registration_number}
                placeholder="e.g. N 1234 AB"
            />
            <InputError message={form.errors.vehicle_registration_number} />
        </div>

        <div class="grid gap-2">
            <Label for="password">Password</Label>
            <PasswordInput
                id="password"
                required
                bind:value={form.password}
                placeholder="Password"
            />
            <InputError message={form.errors.password} />
        </div>

        <div class="grid gap-2">
            <Label for="password_confirmation">Confirm password</Label>
            <PasswordInput
                id="password_confirmation"
                required
                bind:value={form.password_confirmation}
                placeholder="Confirm password"
            />
            <InputError message={form.errors.password_confirmation} />
        </div>

        <Button type="submit" class="mt-2 w-full" disabled={form.processing}>
            {#if form.processing}<Spinner />{/if}
            Apply as Driver
        </Button>
    </form>

    <div class="text-center text-sm text-muted-foreground">
        Already have an account?
        <TextLink href={login()} class="underline underline-offset-4">
            Log in
        </TextLink>
    </div>
</div>
