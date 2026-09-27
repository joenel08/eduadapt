<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>EduAdapt · @yield('page_title', 'Dashboard')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('images/logo_icon.png') }}">
</head>

<body data-page="@yield('page')">

    <div class="container">
        {{-- Sidebar overlay --}}
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        {{-- SIDEBAR (partial) --}}
        @include($roleViewPath . '.partials.sidebar')

        <main class="main-content" id="mainContent">
            {{-- TOP BAR (partial) --}}
            @include($roleViewPath . '.partials.topbar')

            {{-- MAIN CONTENT (yielded from child views) --}}
            <section class="content">
                @yield('content')
            </section>
        </main>
    </div>

    {{-- Global modals (if any) --}}

    {{-- Global Confirmation Modal --}}
    <div class="modal-backdrop" id="confirmModal">
        <div class="modal" style="max-width:420px;">
            <div class="modal-header" style="background:#fef2f2; border-bottom:1px solid #fecaca;">
                <h3 class="modal-title" id="confirmTitle" style="color:#991b1b;">
                    <i class="fas fa-exclamation-triangle"></i> Confirm Action
                </h3>
                <button class="modal-close" onclick="closeConfirmModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage" style="font-size:14px;color:#374151;line-height:1.6;">
                    Are you sure you want to proceed?
                </p>
                <div style="display:flex;gap:12px;justify-content:flex-end;margin-top:20px;">
                    <button type="button" class="secondary-button" onclick="closeConfirmModal()">Cancel</button>
                    <button type="button" class="primary-button" id="confirmOkBtn"
                        style="background:#dc2626;" onclick="confirmProceed()">
                        <i class="fas fa-check"></i> Yes, Proceed
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Global Toast Container --}}
    <div id="toastContainer" style="position:fixed; top:20px; right:20px; z-index:99999; display:flex; flex-direction:column; gap:10px;"></div>

    {{-- Global JS: Toast + Confirm + Auto-handler --}}
    <script>
     

        // ---------- Confirm Modal ----------
        let _confirmCallback = null;

        window.showConfirm = function(message, callback, options = {}) {
            document.getElementById('confirmMessage').textContent = message;
            document.getElementById('confirmTitle').innerHTML =
                `<i class="fas fa-exclamation-triangle"></i> ${options.title || 'Confirm Action'}`;
            const okBtn = document.getElementById('confirmOkBtn');
            okBtn.innerHTML = `<i class="fas fa-check"></i> ${options.okText || 'Yes, Proceed'}`;
            okBtn.style.background = options.danger !== false ? '#dc2626' : '#0066CC';

            _confirmCallback = callback;
            document.getElementById('confirmModal').classList.add('active');
        };

        window.closeConfirmModal = function() {
            document.getElementById('confirmModal').classList.remove('active');
            _confirmCallback = null;
        };

        window.confirmProceed = function() {
            if (typeof _confirmCallback === 'function') {
                _confirmCallback();
            }
            closeConfirmModal();
        };

        // ---------- Auto-handler for forms with data-confirm ----------
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (form.dataset.confirm && !form.dataset.confirmed) {
                e.preventDefault();
                window.showConfirm(form.dataset.confirm, () => {
                    form.dataset.confirmed = '1';
                    form.submit();
                }, {
                    title: form.dataset.confirmTitle || 'Confirm',
                    okText: form.dataset.confirmOk || 'Yes, Proceed',
                });
            }
        });

        // ---------- Auto-handler for links with data-confirm ----------
        document.addEventListener('click', function(e) {
            const link = e.target.closest('a[data-confirm]');
            if (link) {
                e.preventDefault();
                window.showConfirm(link.dataset.confirm, () => {
                    window.location.href = link.href;
                }, {
                    title: link.dataset.confirmTitle || 'Confirm',
                    okText: link.dataset.confirmOk || 'Yes, Proceed',
                });
            }
        });
    </script>


    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <script src="{{ asset('js/style.js') }}"></script>
    @stack('scripts')
</body>

</html>