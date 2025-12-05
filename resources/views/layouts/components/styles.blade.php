
        <!-- Choices JS -->
        <script src="{{asset('build/assets/libs/choices.js/public/assets/scripts/choices.min.js')}}"></script>

        <!-- Main Theme Js -->
        @vite('resources/assets/js/main.js')
        
        <!-- Bootstrap Css -->
        <link id="style" href="{{asset('build/assets/libs/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" >

        <!-- Node Waves Css -->
        <link href="{{asset('build/assets/libs/node-waves/waves.min.css')}}" rel="stylesheet" > 

        <!-- Simplebar Css -->
        <link href="{{asset('build/assets/libs/simplebar/simplebar.min.css')}}" rel="stylesheet" >
        
        <!-- Color Picker Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/@simonwep/pickr/themes/nano.min.css')}}">

        <!-- Choices Css -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/choices.js/public/assets/styles/choices.min.css')}}">

        <!-- FlatPickr CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/flatpickr/flatpickr.min.css')}}">

        <!-- Auto Complete CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/@tarekraafat/autocomplete.js/css/autoComplete.css')}}">
        
        <!-- Select2 CSS -->
        <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
        <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />

        <!-- DataTables CSS -->
        <link rel="stylesheet" href="{{ asset('build/assets/libs/datatables.net-bs5/css/dataTables.bootstrap5.min.css') }}">
        <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.5.0/css/responsive.bootstrap5.min.css">
        <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">

        <!-- Sweet Alert CSS -->
        <link rel="stylesheet" href="{{asset('build/assets/libs/sweetalert2/sweetalert2.min.css')}}">

        <style>
            table.dataTable.dtr-inline.collapsed>tbody>tr>td.dtr-control:before, 
            table.dataTable.dtr-inline.collapsed>tbody>tr>th.dtr-control:before { 
                margin-right: .5em; 
                display: inline-flex;
                justify-content: center;
                align-items: center;
                background-color: #fd7e14;
                color: white;
                content: "+"; 
                font-family: "Courier New", Courier, monospace;
                font-size: 16px;
                font-weight: bold;
                height: 20px; 
                width: 20px; 
                border-radius: 50%; 
                line-height: 1;
                box-shadow: 0 0 0 2px rgba(253, 126, 20, 0.25);
            }
        </style>
        
