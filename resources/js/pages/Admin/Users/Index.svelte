<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import Pagination from '@/components/Pagination.svelte';
    import { router, usePage } from '@inertiajs/svelte';
    import { fade } from 'svelte/transition';

    let { users, filters } = $props();
    let authUser = $derived(usePage().props.auth?.user as any);

    let search = $state(filters.search ?? '');
    let role = $state(filters.role ?? '');
    let searchTimeout: any;

    let showModal = $state(false);
    let modalMode = $state<'create' | 'edit'>('create');
    let selectedUser = $state<any>(null);

    let form = $state({
        firstname: '',
        lastname: '',
        email: '',
        phone: '',
        password: '',
        role: 'admin',
        status: 'active',
    });

    let errors = $state<any>({});
    let processing = $state(false);

    function resetForm() {
        form = {
            firstname: '',
            lastname: '',
            email: '',
            phone: '',
            password: '',
            role: 'admin',
            status: 'active',
        };
        errors = {};
    }

    function openCreateModal() {
        modalMode = 'create';
        resetForm();
        showModal = true;
    }

    function openEditModal(user: any) {
        modalMode = 'edit';
        selectedUser = user;
        form = {
            firstname: user.firstname,
            lastname: user.lastname || '',
            email: user.email,
            phone: user.phone,
            password: '',
            role: user.role,
            status: user.status,
        };
        errors = {};
        showModal = true;
    }

    function handleSubmit() {
        processing = true;
        if (modalMode === 'create') {
            router.post('/admin/users', form, {
                onSuccess: () => {
                    showModal = false;
                    resetForm();
                },
                onError: (err) => (errors = err),
                onFinish: () => (processing = false),
            });
        } else {
            router.put(`/admin/users/${selectedUser.id}`, form, {
                onSuccess: () => {
                    showModal = false;
                    resetForm();
                },
                onError: (err) => (errors = err),
                onFinish: () => (processing = false),
            });
        }
    }

    function deleteUser(id: number) {
        if (id === authUser.id) {
            alert('You cannot delete your own account.');
            return;
        }
        if (confirm('Are you sure you want to delete this user?')) {
            router.delete(`/admin/users/${id}`);
        }
    }

    $effect(() => {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            router.get(
                '/admin/users',
                { search, role },
                {
                    preserveState: true,
                    replace: true,
                },
            );
        }, 300);
    });

    let userList = $derived(users.data);
</script>

<AppHead title="User Management" />

<AdminLayout>
    <div class="py-3">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h4 class="mb-0">User Management</h4>
                <p class="text-muted mb-0">
                    Manage system administrators and drivers
                </p>
            </div>
            <button
                onclick={openCreateModal}
                class="btn btn-primary d-flex align-items-center gap-1"
            >
                <i class="ti ti-plus fs-18"></i>
                Add User
            </button>
        </div>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="p-3 border-bottom">
                    <div class="row g-2">
                        <div class="col-md-4">
                            <div class="input-group">
                                <span
                                    class="input-group-text bg-transparent border-end-0"
                                >
                                    <i class="ti ti-search text-muted"></i>
                                </span>
                                <input
                                    type="text"
                                    class="form-control border-start-0 ps-0"
                                    placeholder="Search name, email, phone..."
                                    bind:value={search}
                                />
                            </div>
                        </div>
                        <div class="col-md-2">
                            <select class="form-select" bind:value={role}>
                                <option value="">All Roles</option>
                                <option value="admin">Admin</option>
                                <option value="driver">Driver</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table
                        class="table table-hover table-centered mb-0 text-nowrap"
                    >
                        <thead class="bg-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th>Status</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            {#each userList as user (user.id)}
                                <tr>
                                    <td>
                                        <div
                                            class="d-flex align-items-center gap-2"
                                        >
                                            <div
                                                class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center"
                                                style="width: 32px; height: 32px;"
                                            >
                                                {user.firstname.charAt(0)}
                                            </div>
                                            <div>
                                                <span
                                                    class="fw-medium d-block text-dark"
                                                >
                                                    {user.firstname}
                                                    {user.lastname || ''}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{user.email}</td>
                                    <td>{user.phone}</td>
                                    <td>
                                        <span
                                            class="badge bg-{user.role ===
                                            'admin'
                                                ? 'purple'
                                                : 'info'}-subtle text-{user.role ===
                                            'admin'
                                                ? 'purple'
                                                : 'info'} text-capitalize"
                                        >
                                            {user.role}
                                        </span>
                                    </td>
                                    <td>
                                        <span
                                            class="badge bg-{user.status ===
                                            'active'
                                                ? 'success'
                                                : 'danger'}-subtle text-{user.status ===
                                            'active'
                                                ? 'success'
                                                : 'danger'}"
                                        >
                                            {user.status}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <div
                                            class="d-flex align-items-center justify-content-center gap-2"
                                        >
                                            <button
                                                onclick={() =>
                                                    openEditModal(user)}
                                                class="btn btn-sm btn-icon btn-soft-primary"
                                                title="Edit User"
                                            >
                                                <i class="ti ti-edit"></i>
                                            </button>
                                            {#if user.id !== authUser.id}
                                                <button
                                                    onclick={() =>
                                                        deleteUser(user.id)}
                                                    class="btn btn-sm btn-icon btn-soft-danger"
                                                    title="Delete User"
                                                >
                                                    <i class="ti ti-trash"></i>
                                                </button>
                                            {/if}
                                        </div>
                                    </td>
                                </tr>
                            {:else}
                                <tr>
                                    <td colspan="6" class="text-center py-5">
                                        <div class="text-muted">
                                            No users found.
                                        </div>
                                    </td>
                                </tr>
                            {/each}
                        </tbody>
                    </table>
                </div>

                <Pagination links={users.links} />
            </div>
        </div>
    </div>
</AdminLayout>

{#if showModal}
    <div
        class="modal fade show d-block"
        tabindex="-1"
        transition:fade={{ duration: 200 }}
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">
                        {modalMode === 'create' ? 'Add New User' : 'Edit User'}
                    </h5>
                    <button
                        type="button"
                        class="btn-close"
                        onclick={() => (showModal = false)}
                    ></button>
                </div>
                <div class="modal-body p-4">
                    <form
                        onsubmit={(e) => {
                            e.preventDefault();
                            handleSubmit();
                        }}
                    >
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    bind:value={form.firstname}
                                    placeholder="Enter first name"
                                />
                                {#if errors.firstname}
                                    <div class="text-danger small">
                                        {errors.firstname}
                                    </div>
                                {/if}
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name</label>
                                <input
                                    type="text"
                                    class="form-control"
                                    bind:value={form.lastname}
                                    placeholder="Enter last name"
                                />
                                {#if errors.lastname}
                                    <div class="text-danger small">
                                        {errors.lastname}
                                    </div>
                                {/if}
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Email Address</label>
                            <input
                                type="email"
                                class="form-control"
                                bind:value={form.email}
                                placeholder="name@example.com"
                            />
                            {#if errors.email}
                                <div class="text-danger small">
                                    {errors.email}
                                </div>
                            {/if}
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Phone Number</label>
                            <input
                                type="text"
                                class="form-control"
                                bind:value={form.phone}
                                placeholder="e.g. 08123456789"
                            />
                            {#if errors.phone}
                                <div class="text-danger small">
                                    {errors.phone}
                                </div>
                            {/if}
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                Password
                                {#if modalMode === 'edit'}
                                    <small class="text-muted"
                                        >(Leave blank to keep current)</small
                                    >
                                {/if}
                            </label>
                            <input
                                type="password"
                                class="form-control"
                                bind:value={form.password}
                                placeholder="Enter password"
                            />
                            {#if errors.password}
                                <div class="text-danger small">
                                    {errors.password}
                                </div>
                            {/if}
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <select
                                    class="form-select"
                                    bind:value={form.role}
                                >
                                    <option value="admin">Admin</option>
                                    <option value="driver">Driver</option>
                                </select>
                                {#if errors.role}
                                    <div class="text-danger small">
                                        {errors.role}
                                    </div>
                                {/if}
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select
                                    class="form-select"
                                    bind:value={form.status}
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                                {#if errors.status}
                                    <div class="text-danger small">
                                        {errors.status}
                                    </div>
                                {/if}
                            </div>
                        </div>

                        {#if form.role === 'driver' && modalMode === 'create'}
                            <div class="alert alert-info py-2 small">
                                <i class="ti ti-info-circle me-1"></i>
                                Adding as a driver will automatically create a record
                                in the drivers table.
                            </div>
                        {/if}

                        <div class="d-flex justify-content-end gap-2 mt-4">
                            <button
                                type="button"
                                class="btn btn-light"
                                onclick={() => (showModal = false)}
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="btn btn-primary"
                                disabled={processing}
                            >
                                {#if processing}
                                    <span
                                        class="spinner-border spinner-border-sm me-1"
                                    ></span>
                                {/if}
                                {modalMode === 'create'
                                    ? 'Create User'
                                    : 'Save Changes'}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
{/if}

<style>
    .modal.show.d-block {
        background: rgba(0, 0, 0, 0.5);
    }
    .btn-icon {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 0;
    }
</style>
