@extends('layouts.teacher-student')

@section('page_title', 'My Classes')
@section('page', 'classes')

@section('content')
<div class="page-header" style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
    <div>
        <h1 style="font-size:28px;font-weight:700;">My Classes</h1>
        <p style="color:#999;">Manage your assigned classes and view student lists.</p>
    </div>
   
</div>

@if($classes->count())
<div class="classes-grid" id="classesGrid">
    @foreach($classes as $class)
    <div class="class-card">
        <div class="class-card-menu">
            <button class="menu-btn" onclick="toggleMenu(event, {{ $class->id }})">
                <i class="fas fa-ellipsis-v"></i>
            </button>
            <div class="menu-dropdown" id="menu-{{ $class->id }}">
               <button class="menu-item" onclick="window.location.href='{{ route('teacher.class-details.show', ['assignment' => $class->assignment_id]) }}'">
    <i class="fas fa-eye"></i> View Students
</button>
            </div>
        </div>
        <div class="class-card-header">
            <div class="class-card-header-left">
                <div class="class-card-icon">
                    <i class="fas fa-book"></i>
                </div>
                <div>
                    <div class="class-card-title">{{ $class->subject }}</div>
                    <div class="class-card-grade">{{ $class->grade }}</div>
                </div>
            </div>
        </div>
        <div class="class-card-body">
            <div class="class-info-row">
                <div class="class-info-group">
                    <div class="class-info-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="class-info-content">
                        <span class="class-info-label">Students</span>
                        <span class="class-info-value">{{ $class->students }}</span>
                    </div>
                </div>
                <div class="class-info-group">
                    <div class="class-info-content">
                        <span class="class-info-label">Section</span>
                        <span class="class-info-value">{{ $class->section_name }}</span>
                    </div>
                </div>
            </div>
            <div class="class-info-row">
                <div class="class-info-group">
                    <div class="class-info-icon">
                        <i class="fas fa-calendar"></i>
                    </div>
                    <div class="class-info-content">
                        <span class="class-info-label">School Year</span>
                        <span class="class-info-value">{{ $class->school_year }}</span>
                    </div>
                </div>
            </div>
            <div class="class-code-section">
                <span class="class-code-label">Class Code</span>
                <div class="class-code-wrapper">
                    <span class="class-code">{{ $class->code }}</span>
                    <button class="copy-btn" onclick="copyToClipboard('{{ $class->code }}')" type="button" title="Copy code">
                        <i class="fas fa-copy"></i>
                    </button>
                </div>
            </div>
        </div>
        <div class="class-card-footer">
           <a href="{{ route('teacher.class-details.show', $class->assignment_id) }}" class="btn-view"> View Class</a>
        </div>
    </div>
    @endforeach
</div>
@else
<div class="empty-state">
    <div class="empty-state-icon"><i class="fas fa-book"></i></div>
    <div class="empty-state-title">No Classes Assigned</div>
    <div class="empty-state-text">You are not assigned to any classes yet. Contact the administrator.</div>
</div>
@endif
@endsection



@push('scripts')
<script>
    // ============ TOGGLE MENU ============
    function toggleMenu(event, classId) {
        event.stopPropagation();
        const menu = document.getElementById(`menu-${classId}`);
        const allMenus = document.querySelectorAll('.menu-dropdown');
        allMenus.forEach(m => {
            if (m.id !== `menu-${classId}`) {
                m.classList.remove('active');
            }
        });
        menu.classList.toggle('active');
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.class-card-menu')) {
            document.querySelectorAll('.menu-dropdown').forEach(menu => {
                menu.classList.remove('active');
            });
        }
    });

    // ============ VIEW CLASS DETAILS ============
    function viewClassDetails(event, classId) {
        const btn = event.currentTarget;
        const url = btn.dataset.route.replace(':id', classId);
        window.location.href = url;
    }

    // ============ COPY TO CLIPBOARD ============
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Class code copied to clipboard!');
        }).catch(() => {
            // Fallback
            const input = document.createElement('input');
            input.value = text;
            document.body.appendChild(input);
            input.select();
            document.execCommand('copy');
            document.body.removeChild(input);
            alert('Class code copied to clipboard!');
        });
    }
</script>
@endpush