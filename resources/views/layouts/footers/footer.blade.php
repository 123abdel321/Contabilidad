<style>
    .navbuttons {
        overflow: auto;
        overflow-y: hidden;
        display: list-item;
        max-width: 100%;
        white-space: nowrap;
    }

    .navbuttons LI {
        display: inline-block;
        vertical-align: top;
    }
</style>

<div class="footer-navigation">
    <ul class="nav nav-tabs navbuttons" id="footer-navigation">
        <li class="nav-item" id="lista_view_dashboard">
            <div class="nav-link col active seleccionar-view"
                 id="tab-dashboard"
                 onclick="seleccionarView('dashboard')">
                <i class="fas fa-home"></i>&nbsp;Inicios
            </div>
        </li>
    </ul>
</div>