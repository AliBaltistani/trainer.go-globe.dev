
<!-- Scroll To Top -->
         <div class="scrollToTop">
            <span class="arrow lh-1"><i class="ti ti-arrow-big-up fs-18"></i></span>
         </div>
         <div id="responsive-overlay"></div>
         <!-- Scroll To Top -->

         <!-- jQuery JS -->
         <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>

         <!-- Popper JS -->
         <script src="{{asset('build/assets/libs/@popperjs/core/umd/popper.min.js')}}"></script>

         <!-- Bootstrap JS -->
         <script src="{{asset('build/assets/libs/bootstrap/js/bootstrap.bundle.min.js')}}"></script>

         <!-- Node Waves JS-->
         <script src="{{asset('build/assets/libs/node-waves/waves.min.js')}}"></script>

         <!-- Simplebar JS -->
         <script src="{{asset('build/assets/libs/simplebar/simplebar.min.js')}}"></script>
         @vite('resources/assets/js/simplebar.js')

         <!-- Auto Complete JS -->
         <script src="{{asset('build/assets/libs/@tarekraafat/autocomplete.js/autoComplete.min.js')}}"></script>

         <!-- Color Picker JS -->
         <script src="{{asset('build/assets/libs/@simonwep/pickr/pickr.es5.min.js')}}"></script>

         <!-- Date & Time Picker JS -->
        <script src="{{asset('build/assets/libs/flatpickr/flatpickr.min.js')}}"></script>

        <!-- Select2 JS -->
        <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

        <!-- Sweet Alert -->
        <script src="{{asset('build/assets/libs/sweetalert2/sweetalert2.min.js')}}"></script>

        <!-- DataTables JS -->
        <script src="https://cdn.datatables.net/1.13.11/js/jquery.dataTables.min.js"></script>
        <script src="{{asset('build/assets/libs/datatables.net-bs5/js/dataTables.bootstrap5.min.js')}}"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/dataTables.responsive.min.js"></script>
        <script src="https://cdn.datatables.net/responsive/2.5.0/js/responsive.bootstrap5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
        <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

        <!-- Sortable JS -->
        <script src="{{asset('build/assets/libs/sortablejs/Sortable.min.js')}}"></script>
        
        @yield('scripts')
