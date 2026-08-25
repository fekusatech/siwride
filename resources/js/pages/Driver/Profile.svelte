<script lang="ts">
    import { useForm, usePage } from '@inertiajs/svelte';
    import InputError from '@/components/InputError.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import DriverLayout from '../../layouts/DriverLayout.svelte';
    import profileActions from '@/routes/driver/profile';

    const page = usePage();
    const user = $derived(page.props.user as any);
    let previewUrl = $state<string | null>(null);

    const profileForm = useForm({
        firstname: user.firstname,
        lastname: user.lastname,
        email: user.email,
        phone: user.phone || '',
        image: null as File | null,
        _method: 'PUT',
    });

    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    function handleImageChange(event: Event): void {
        const input = event.currentTarget as HTMLInputElement;
        const file = input.files?.[0];

        if (!file) {
            return;
        }

        profileForm.image = file;
        previewUrl = URL.createObjectURL(file);
    }

    function updateProfile(): void {
        profileForm.post(profileActions.update.url(), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => profileForm.reset('image'),
        });
    }

    function updatePassword(): void {
        passwordForm.put(profileActions.password.url(), {
            preserveScroll: true,
            onSuccess: () => passwordForm.reset(),
        });
    }
</script>

<AppHead title="My Profile" />

<DriverLayout>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">My Profile</h1>
            <p class="text-muted mb-0">Manage your personal information and security.</p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h2 class="h5 mb-0">Personal Information</h2></div>
                <div class="card-body">
                    <form onsubmit={(event) => { event.preventDefault(); updateProfile(); }}>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstname" class="form-label">First Name</label>
                                <input id="firstname" class="form-control" bind:value={profileForm.firstname} />
                                <InputError message={profileForm.errors.firstname} />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastname" class="form-label">Last Name</label>
                                <input id="lastname" class="form-control" bind:value={profileForm.lastname} />
                                <InputError message={profileForm.errors.lastname} />
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input id="email" type="email" class="form-control" bind:value={profileForm.email} />
                                <InputError message={profileForm.errors.email} />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input id="phone" class="form-control" bind:value={profileForm.phone} />
                                <InputError message={profileForm.errors.phone} />
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="image" class="form-label">Profile Photo</label>
                            <div class="d-flex align-items-center gap-3">
                                {#if previewUrl || user.image}
                                    <img src={previewUrl || `/storage/${user.image}`} alt="Profile" class="rounded-circle border" style="width: 72px; height: 72px; object-fit: cover;" />
                                {:else}
                                    <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 72px; height: 72px;">{user.firstname?.charAt(0)}</div>
                                {/if}
                                <input id="image" type="file" class="form-control" accept="image/*" onchange={handleImageChange} />
                            </div>
                            <small class="text-muted">Maximum 2MB. JPG, PNG, or WEBP.</small>
                            <InputError message={profileForm.errors.image} />
                        </div>
                        <div class="text-end"><button class="btn btn-primary" disabled={profileForm.processing}>{profileForm.processing ? 'Saving...' : 'Save Changes'}</button></div>
                    </form>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white"><h2 class="h5 mb-0">Change Password</h2></div>
                <div class="card-body">
                    <form onsubmit={(event) => { event.preventDefault(); updatePassword(); }}>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input id="current_password" type="password" class="form-control" bind:value={passwordForm.current_password} />
                            <InputError message={passwordForm.errors.current_password} />
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input id="password" type="password" class="form-control" bind:value={passwordForm.password} />
                                <InputError message={passwordForm.errors.password} />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input id="password_confirmation" type="password" class="form-control" bind:value={passwordForm.password_confirmation} />
                                <InputError message={passwordForm.errors.password_confirmation} />
                            </div>
                        </div>
                        <div class="text-end"><button class="btn btn-outline-danger" disabled={passwordForm.processing}>{passwordForm.processing ? 'Updating...' : 'Update Password'}</button></div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card border-0 shadow-sm text-center">
                <div class="card-body">
                    {#if previewUrl || user.image}
                        <img src={previewUrl || `/storage/${user.image}`} alt="Profile" class="rounded-circle mb-3" style="width: 120px; height: 120px; object-fit: cover;" />
                    {:else}
                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center fw-bold mx-auto mb-3" style="width: 120px; height: 120px; font-size: 2.5rem;">{user.firstname?.charAt(0)}</div>
                    {/if}
                    <h2 class="h5 mb-1">{user.firstname} {user.lastname}</h2>
                    <p class="text-muted mb-0">{user.email}</p>
                    <span class="badge bg-success-subtle text-success mt-3">Driver</span>
                </div>
            </div>
        </div>
    </div>
</DriverLayout>

<style>
    .bg-primary-subtle { background-color: rgba(13, 110, 253, 0.1); }
    .bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
</style>
