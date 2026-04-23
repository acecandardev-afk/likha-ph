@props([
    'id' => 'confirmModal',
    'formId' => 'confirmForm',
    'title' => 'Confirm',
    'message' => '',
    'submitLabel' => 'Confirm',
    'submitVariant' => 'primary',
    'cancelLabel' => 'Cancel',
])

@php
    $btnClass = match ($submitVariant) {
        'danger' => 'btn-danger',
        'success' => 'btn-success',
        'warning' => 'btn-warning',
        'primary' => 'btn-primary',
        'secondary' => 'btn-secondary',
        default => 'btn-primary',
    };
@endphp

<div class="modal fade" id="{{ $id }}" tabindex="-1" aria-labelledby="{{ $id }}Title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h2 class="modal-title h5 fw-semibold mb-0" id="{{ $id }}Title">{{ $title }}</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-2">
                @if($message !== '')
                    <p class="text-body-secondary small mb-0">{{ $message }}</p>
                @endif
                {{ $slot }}
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ $cancelLabel }}</button>
                <button type="submit" form="{{ $formId }}" class="btn {{ $btnClass }}">{{ $submitLabel }}</button>
            </div>
        </div>
    </div>
</div>
