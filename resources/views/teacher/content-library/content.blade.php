{{-- resources/views/teacher/content-library/content.blade.php --}}
@extends('layouts.teacher-student')

@section('page_title', $subject . ' - ' . $week)
@section('page', 'content-library')

@section('content')
<style>
    /* Custom dropdown */
    .dropdown-custom {
        position: relative;
        display: inline-block;
    }

    .dropdown-toggle-custom {
        background: linear-gradient(135deg, #0066CC 0%, #004D99 100%);
        color: white;
        border: none;
        padding: 12px 24px;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }

    .dropdown-toggle-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 102, 204, 0.3);
    }

    .dropdown-arrow {
        font-size: 12px;
        margin-left: 6px;
        transition: transform 0.3s ease;
    }

    .dropdown-custom.open .dropdown-arrow {
        transform: rotate(180deg);
    }

    .dropdown-menu-custom {
        display: none;
        position: absolute;
        top: 100%;
        left: 0;
        margin-top: 8px;
        min-width: 200px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.15);
        list-style: none;
        padding: 8px 0;
        z-index: 1000;
        border: 1px solid #e0e0e0;
    }

    .dropdown-custom.open .dropdown-menu-custom {
        display: block;
    }

    .dropdown-menu-custom li {
        padding: 0;
    }

    .dropdown-menu-custom li a {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 20px;
        color: #333;
        text-decoration: none;
        transition: background 0.2s ease;
        font-size: 14px;
        font-weight: 500;
    }

    .dropdown-menu-custom li a:hover {
        background: #f5f5f5;
        color: #0066CC;
    }

    .dropdown-menu-custom li a i {
        width: 20px;
        color: #0066CC;
    }
</style>
<div class="back-button show" id="backButton">
    <button class="back-btn" id="backBtn">
        <i class="fas fa-arrow-left"></i>
        <span id="backButtonText">
            <a href="{{ route('teacher.content-library.weeks', [$grade, $term, $subject]) }}" class="btn btn-secondary">Back to Weeks</a>
        </span>
    </button>

</div>

<div id="contentItemsContainer">
   

</div>

{{-- New action buttons --}}

{{-- End new action buttons --}}


 @include('teacher.content-library.partials.config-modal')
@endsection

@push('scripts')
<script src="{{ asset('js/content-library/core.js') }}"></script>
<script src="{{ asset('js/content-library/configuration.js') }}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const path = {
            grade: '{{ $grade }}',
            term: '{{ $term }}',
            subject: '{{ $subject }}',
            week: '{{ $week }}',
        };
        if (typeof loadContentForWeek === 'function') {
            loadContentForWeek(path);
        }
   
        
    });
</script>
@endpush