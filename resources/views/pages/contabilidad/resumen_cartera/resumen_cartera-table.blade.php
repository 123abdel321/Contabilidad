<table id="resumenCarteraInformeTable" class="table nowrap table-bordered display responsive" width="100%">
    <thead>
        <tr>
            <th style="border-radius: 15px 0px 0px 0px !important;">Documento</th>
            <th>Nombre</th>
            <th>Ubicacion</th>
            @for ($i = 1; $i <= 30; $i++)
                <th
                    style="max-width: 120px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                    data-toggle="popover"
                    data-html="true"
                    id="cuenta_{{ $i }}"
                >
                    cuenta_{{ $i }}
                </th>
            @endfor
            <th>Saldo Final</th>
            <th style="border-radius: 0px 15px 0px 0px !important;">Mora</th>
        </tr>
    </thead>
</table>