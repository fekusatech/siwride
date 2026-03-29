<script lang="ts">
    import AdminLayout from '../../layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { useForm, usePage } from '@inertiajs/svelte';
    import InputError from '@/components/InputError.svelte';

    const page = usePage();
    const user = page.props.user as any;

    const profileForm = useForm({
        firstname: user.firstname,
        lastname: user.lastname,
        email: user.email,
        phone: user.phone || '',
        image: null as File | null,
        _method: 'PUT' // For multipart/form-data with PUT
    });

    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });

    function updateProfile() {
        profileForm.post('/admin/profile', {
            preserveScroll: true,
            onSuccess: () => profileForm.reset('image')
        });
    }

    function updatePassword() {
        passwordForm.put('/admin/profile/password', {
            preserveScroll: true,
            onSuccess: () => passwordForm.reset()
        });
    }

    function handleImageChange(e: Event) {
        const target = e.target as HTMLInputElement;
        if (target.files && target.files.length > 0) {
            profileForm.image = target.files[0];
        }
    }
</script>

<AppHead title="My Profile" />

<AdminLayout>
    <div class="row">
        <div class="col-12">
            <div class="page-title-head d-flex align-items-sm-center flex-sm-row flex-column">
                <div class="flex-grow-1">
                    <h4 class="fs-18 text-uppercase fw-bold m-0">My Account</h4>
                    <p class="text-muted mb-0">Manage your profile and security settings</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Personal Information -->
        <div class="col-xl-8">
            <div class="card shadow-sm border-0">
                <div class="card-header border-bottom border-dashed">
                    <h4 class="header-title mb-0">Personal Information</h4>
                </div>
                <div class="card-body">
                    <form onsubmit={(e) => { e.preventDefault(); updateProfile(); }}>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstname" class="form-label">First Name</label>
                                <input type="text" id="firstname" class="form-control" bind:value={profileForm.firstname} placeholder="Enter your first name">
                                <InputError message={profileForm.errors.firstname} />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastname" class="form-label">Last Name</label>
                                <input type="text" id="lastname" class="form-control" bind:value={profileForm.lastname} placeholder="Enter your last name">
                                <InputError message={profileForm.errors.lastname} />
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" id="email" class="form-control" bind:value={profileForm.email} placeholder="name@example.com">
                                <InputError message={profileForm.errors.email} />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">Phone Number</label>
                                <input type="text" id="phone" class="form-control" bind:value={profileForm.phone} placeholder="e.g., +628123456789">
                                <InputError message={profileForm.errors.phone} />
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="profile_image" class="form-label">Profile Image</label>
                            <div class="d-flex align-items-center gap-3">
                                <div class="avatar-lg d-flex align-items-center justify-content-center flex-shrink-0" style="width: 80px; height: 80px;">
                                    {#if user.image}
                                        <img src={`/storage/${user.image}`} class="img-thumbnail rounded-circle" alt="Current Avatar" style="width: 80px; height: 80px; object-fit: cover; flex-shrink: 0;">
                                    {:else}
                                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-24 d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; min-width: 80px; min-height: 80px;">
                                            {user.firstname.charAt(0)}
                                        </div>
                                    {/if}
                                </div>
                                <input type="file" id="profile_image" class="form-control" onchange={handleImageChange} accept="image/*">
                            </div>
                            <small class="text-muted">Max size: 2MB. Format: JPG, PNG, WEBP.</small>
                            <InputError message={profileForm.errors.image} />
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary px-4" disabled={profileForm.processing}>
                                {profileForm.processing ? 'Saving...' : 'Save Changes'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Security Section -->
            <div class="card shadow-sm border-0 mt-4">
                <div class="card-header border-bottom border-dashed">
                    <h4 class="header-title mb-0">Security & Password</h4>
                </div>
                <div class="card-body">
                    <form onsubmit={(e) => { e.preventDefault(); updatePassword(); }}>
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Current Password</label>
                            <input type="password" id="current_password" class="form-control" bind:value={passwordForm.current_password} placeholder="Enter your current password">
                            <InputError message={passwordForm.errors.current_password} />
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">New Password</label>
                                <input type="password" id="password" class="form-control" bind:value={passwordForm.password} placeholder="New password">
                                <InputError message={passwordForm.errors.password} />
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="password_confirmation" class="form-label">Confirm New Password</label>
                                <input type="password" id="password_confirmation" class="form-control" bind:value={passwordForm.password_confirmation} placeholder="Confirm new password">
                                <InputError message={passwordForm.errors.password_confirmation} />
                            </div>
                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-soft-danger px-4" disabled={passwordForm.processing}>
                                {passwordForm.processing ? 'Updating...' : 'Update Password'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Account Summary Sidebar -->
        <div class="col-xl-4">
            <div class="card shadow-sm border-0 text-center">
                <div class="card-body pb-0">
                    <div class="avatar-xl mx-auto mb-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 120px; height: 120px;">
                        {#if user.image}
                            <img src={`/storage/${user.image}`} class="rounded-circle img-thumbnail" alt="User Avatar" style="width: 120px; height: 120px; object-fit: cover; flex-shrink: 0;">
                        {:else}
                            <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-40 d-flex align-items-center justify-content-center mx-auto" style="width: 120px; height: 120px; min-width: 120px; min-height: 120px;">
                                {user.firstname.charAt(0)}
                            </div>
                        {/if}
                    </div>
                    <h4 class="mb-1 fw-bold">{user.firstname} {user.lastname}</h4>
                    <p class="text-muted"><i class="ti ti-mail me-1"></i> {user.email}</p>
                    <div class="badge bg-success-subtle text-success px-3 py-2 mb-3">
                        Administrator
                    </div>
                </div>
                <div class="card-footer bg-light bg-opacity-50 border-0 p-3 mt-3">
                    <div class="row text-center">
                        <div class="col-6 border-end">
                            <h5 class="fw-bold mb-0">Active</h5>
                            <small class="text-muted">Status</small>
                        </div>
                        <div class="col-6">
                            <h5 class="fw-bold mb-0">{new Date(user.created_at).getFullYear()}</h5>
                            <small class="text-muted">Member Since</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>

<style>
    .bg-primary-subtle { background-color: rgba(79, 70, 229, 0.1); }
    .bg-success-subtle { background-color: rgba(16, 185, 129, 0.1); }
    .btn-soft-danger {
        background-color: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border: none;
    }
    .btn-soft-danger:hover {
        background-color: #ef4444;
        color: white;
    }
</style>
