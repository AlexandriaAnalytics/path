<x-filament::page>
    <div>
        {{ $this->table }}
        <input type="text" id="cambioValue" value="{{ $this->cambio }}" hidden />
    </div>

    <script>
        let ultimo = document.querySelector('#cambioValue').value;

        const verificar = () => {
            const actual = document.querySelector('#cambioValue').value;

            if (actual !== ultimo) {
                ultimo = actual;
                window.location.reload();
            }
        };

        setInterval(verificar, 10);
    </script>

</x-filament::page>