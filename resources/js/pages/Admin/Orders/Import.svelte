<script lang="ts">
    import AdminLayout from '@/layouts/AdminLayout.svelte';
    import AppHead from '@/components/AppHead.svelte';
    import { router } from '@inertiajs/svelte';
    import { fade } from 'svelte/transition';

    let fileInput: HTMLInputElement;
    let selectedFile: File | null = null;
    let isImporting = $state(false);
    let importError = $state('');
    let dragActive = $state(false);

    function handleDragEnter(e: DragEvent) {
        e.preventDefault();
        e.stopPropagation();
        dragActive = true;
    }

    function handleDragLeave(e: DragEvent) {
        e.preventDefault();
        e.stopPropagation();
        dragActive = false;
    }

    function handleDrop(e: DragEvent) {
        e.preventDefault();
        e.stopPropagation();
        dragActive = false;

        const files = e.dataTransfer?.files;
        if (files && files.length > 0) {
            selectedFile = files[0];
            importError = '';
        }
    }

    function handleFileSelect(e: Event) {
        const target = e.target as HTMLInputElement;
        if (target.files && target.files.length > 0) {
            selectedFile = target.files[0];
            importError = '';
        }
    }

    function downloadTemplate() {
        window.location.href = '/admin/orders/import/template';
    }

    function handleImport() {
        if (!selectedFile) {
            importError = 'Please select a file to import';
            return;
        }

        const validTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'text/csv',
        ];
        if (!validTypes.includes(selectedFile.type)) {
            importError =
                'Invalid file format. Please use .xlsx, .xls, or .csv';
            return;
        }

        if (selectedFile.size > 10 * 1024 * 1024) {
            importError = 'File size exceeds 10MB limit';
            return;
        }

        isImporting = true;
        importError = '';

        const formData = new FormData();
        formData.append('file', selectedFile);

        router.post('/admin/orders/import', formData, {
            onFinish: () => {
                isImporting = false;
            },
            onError: (errors: any) => {
                importError = errors.file?.[0] || 'Failed to import orders';
                isImporting = false;
            },
        });
    }

    function resetForm() {
        selectedFile = null;
        importError = '';
        if (fileInput) {
            fileInput.value = '';
        }
    }
</script>

<AppHead title="Import Orders" />

<AdminLayout>
    <div class="py-3">
        <div class="mb-4">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h4 class="mb-0">Import Orders</h4>
                    <p class="text-muted mb-0">
                        Upload Excel file to import multiple orders at once
                    </p>
                </div>
                <a
                    href="/admin/orders"
                    class="btn btn-outline-secondary d-flex align-items-center gap-1"
                >
                    <i class="ti ti-arrow-left fs-18"></i>
                    Back to Orders
                </a>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-4">
                            <h6 class="card-title mb-3">Upload Excel File</h6>

                            <!-- Error message -->
                            {#if importError}
                                <div
                                    class="alert alert-danger alert-dismissible fade show"
                                    role="alert"
                                    transition:fade
                                >
                                    <i class="ti ti-alert-circle me-2"></i>
                                    <strong>Error:</strong>
                                    {importError}
                                    <button
                                        type="button"
                                        class="btn-close"
                                        data-bs-dismiss="alert"
                                        aria-label="Close"
                                    ></button>
                                </div>
                            {/if}

                            <!-- Drag & Drop Area -->
                            <div
                                class="border-2 border-dashed rounded p-4 text-center"
                                class:border-primary={dragActive}
                                class:bg-light={dragActive}
                                ondragenter={handleDragEnter}
                                ondragleave={handleDragLeave}
                                ondrop={handleDrop}
                                ondragover={(e) => {
                                    e.preventDefault();
                                    e.stopPropagation();
                                }}
                                style="transition: all 0.2s ease; cursor: pointer; border-color: {dragActive
                                    ? '#007bff'
                                    : '#dee2e6'};"
                            >
                                <div class="py-4">
                                    <i
                                        class="ti ti-file-spreadsheet fs-48 text-primary mb-3"
                                        style="display: block;"
                                    ></i>
                                    <h6 class="mb-2">
                                        Drag and drop your Excel file here
                                    </h6>
                                    <p class="text-muted small mb-3">or</p>
                                    <button
                                        type="button"
                                        class="btn btn-primary"
                                        onclick={() => fileInput?.click()}
                                        disabled={isImporting}
                                    >
                                        <i class="ti ti-folder-open me-2"></i>
                                        Browse File
                                    </button>
                                    <input
                                        bind:this={fileInput}
                                        type="file"
                                        accept=".xlsx,.xls,.csv"
                                        onchange={handleFileSelect}
                                        style="display: none;"
                                    />
                                </div>
                            </div>

                            <!-- Selected file info -->
                            {#if selectedFile}
                                <div
                                    class="mt-4 p-3 bg-light rounded d-flex align-items-center justify-content-between"
                                >
                                    <div
                                        class="d-flex align-items-center gap-2"
                                    >
                                        <i
                                            class="ti ti-file-check text-success fs-24"
                                        ></i>
                                        <div>
                                            <p class="mb-0 fw-bold">
                                                {selectedFile.name}
                                            </p>
                                            <p class="text-muted small mb-0">
                                                {(
                                                    selectedFile.size / 1024
                                                ).toFixed(2)} KB
                                            </p>
                                        </div>
                                    </div>
                                    <button
                                        type="button"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick={resetForm}
                                        disabled={isImporting}
                                    >
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </div>
                            {/if}

                            <!-- Action buttons -->
                            <div class="d-flex gap-2 mt-4">
                                <button
                                    type="button"
                                    class="btn btn-success flex-grow-1"
                                    onclick={handleImport}
                                    disabled={!selectedFile || isImporting}
                                >
                                    {#if isImporting}
                                        <span
                                            class="spinner-border spinner-border-sm me-2"
                                        ></span>
                                        Importing...
                                    {:else}
                                        <i class="ti ti-upload me-2"></i>
                                        Import Orders
                                    {/if}
                                </button>
                                <button
                                    type="button"
                                    class="btn btn-outline-secondary"
                                    onclick={resetForm}
                                    disabled={isImporting || !selectedFile}
                                >
                                    <i class="ti ti-x me-2"></i>
                                    Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Template Info -->
                <div class="col-lg-4">
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Template</h6>
                            <p class="text-muted small mb-3">
                                Download the Excel template to see the required
                                format and column headers.
                            </p>
                            <button
                                type="button"
                                class="btn btn-outline-primary w-100"
                                onclick={downloadTemplate}
                            >
                                <i class="ti ti-download me-2"></i>
                                Download Template
                            </button>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Requirements</h6>
                            <ul class="list-unstyled small">
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"
                                    ></i>
                                    <strong>File format:</strong> .xlsx, .xls, or
                                    .csv
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"
                                    ></i>
                                    <strong>Max size:</strong> 10MB
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"
                                    ></i>
                                    <strong>Required columns:</strong> Date, Time,
                                    Customer Name, Customer Phone, Pickup Address,
                                    Dropoff Address, Price
                                </li>
                                <li class="mb-2">
                                    <i class="ti ti-check text-success me-2"
                                    ></i>
                                    <strong>Date format:</strong> YYYY-MM-DD or DD/MM/YYYY
                                </li>
                                <li>
                                    <i class="ti ti-check text-success me-2"
                                    ></i>
                                    <strong>Time format:</strong> HH:MM
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4 bg-light">
                        <div class="card-body">
                            <h6 class="card-title mb-3">Optional Fields</h6>
                            <ul class="list-unstyled small text-muted">
                                <li class="mb-2">• Flight Number</li>
                                <li class="mb-2">• Driver Code (nid)</li>
                                <li class="mb-2">• Vehicle Plate</li>
                                <li class="mb-2">• Passengers (default: 1)</li>
                                <li class="mb-2">
                                    • Parking Gas Fee (default: 0)
                                </li>
                                <li class="mb-2">
                                    • Status (pending, completed, cancelled)
                                </li>
                                <li>
                                    • Booking Code & Order Number
                                    (auto-generated)
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</AdminLayout>

<style>
    .border-2 {
        border-width: 2px !important;
    }

    .border-dashed {
        border-style: dashed !important;
    }
</style>
