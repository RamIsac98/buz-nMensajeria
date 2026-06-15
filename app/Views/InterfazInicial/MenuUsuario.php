<?= $this->extend('layouts/base') ?>

<?= $this->section('title') ?>Panel Principal<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<style>
    .main-title {
        color: var(--azul-oscuro);
        font-weight: bold;
        margin-top: 35px;
        margin-bottom: 10px;
        font-size: 1.75rem;
    }
    .panel-card {
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .panel-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container-fluid px-4">
    <div class="mb-4">
        <h2 class="main-title">Panel de Control Principal</h2>
        <p class="text-muted" style="font-size: 1.05rem;">
            Selecciona una de las opciones disponibles según tus permisos para realizar una solicitud en el sistema:
        </p>
    </div>
</div>
<?= $this->endSection() ?>