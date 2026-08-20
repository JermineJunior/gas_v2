@if (session('success'))
    <script>
        Swal.fire({
            toast: true,
            position: 'bottom-end',
            icon: 'success',
            title: '{{ session('success') }}',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });
    </script>
@elseif ($errors->any())
    <script>
        Swal.fire({
            toast: true,
            position: 'bottom-end', // أسفل يمين
            icon: 'error',
            title: '{{ $errors->first() }}',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });
    </script>
@elseif (session('error'))
    <script>
        Swal.fire({
            toast: true,
            position: 'bottom-end',
            icon: 'error',
            title: '{{ session('error') }}',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true
        });
    </script>
@endif
