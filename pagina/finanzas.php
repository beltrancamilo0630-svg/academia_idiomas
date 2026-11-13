<?php
require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/app/config/database.php';

require_role([1]);

$database = new Database();
$conn = $database->conectar();

$stmt = $conn->prepare('SELECT nombre FROM usuarios WHERE id_usuario = ?');
$stmt->execute([(int) $_SESSION['id_usuario']]);
$usuarioActual = $stmt->fetch() ?: ['nombre' => $_SESSION['usuario']];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="Finanzas y reportes de la Academia de Idiomas: pagos, cartera y consolidado mensual.">
  <meta name="theme-color" content="#0F172A">
  <title>Finanzas y reportes | Academia de Idiomas</title>
  <link rel="icon" href="assets/favicon.svg" type="image/svg+xml">
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/layout.css">
  <link rel="stylesheet" href="css/pages.css">
</head>
<body data-role="admin" data-page="finanzas" data-title="Finanzas y reportes" data-subtitle="Pagos, cartera y consolidado financiero" data-user-name="<?= e($usuarioActual['nombre']) ?>" data-user-role="Administrador" data-user-initials="<?= e(initials($usuarioActual['nombre'])) ?>">
  <a class="skip-link" href="#main">Saltar al contenido</a>

  <div class="app-shell">
    <aside class="sidebar" id="sidebar"></aside>

    <div class="app-main">
      <header class="topbar" id="topbar"></header>

      <main class="page" id="main">
        <div class="page-head">
          <div>
            <span class="eyebrow">Módulo 04</span>
            <h1>Finanzas y reportes</h1>
            <p>Controla los ingresos, valida comprobantes y consulta el consolidado mensual de la academia.</p>
          </div>
          <div class="page-actions">
            <button class="btn btn-outline" type="button" data-toast="Reporte CSV generado (demo).">
              <span data-icon="download"></span>
              Exportar CSV
            </button>
            <button class="btn btn-primary" type="button" data-modal-open="paymentModal">
              <span data-icon="plus"></span>
              Registrar pago
            </button>
          </div>
        </div>

        <nav class="anchor-nav" aria-label="Secciones de finanzas">
          <a class="is-active" href="#pagos"><span data-icon="receipt"></span> Pagos</a>
          <a href="#cartera"><span data-icon="alert-circle"></span> Cartera</a>
          <a href="#reportes"><span data-icon="bar-chart"></span> Reportes</a>
        </nav>

        <!-- KPIs -->
        <section class="kpi-grid" aria-label="Indicadores financieros">
          <article class="kpi-card">
            <div class="kpi-top">
              <span class="kpi-label">Recaudado en noviembre</span>
              <span class="kpi-icon success"><span data-icon="wallet"></span></span>
            </div>
            <div class="kpi-value">$18.640.000</div>
            <div class="kpi-meta">
              <span class="kpi-trend up"><span data-icon="trending-up"></span> +8.4%</span>
              vs. octubre
            </div>
          </article>

          <article class="kpi-card">
            <div class="kpi-top">
              <span class="kpi-label">Pagos registrados</span>
              <span class="kpi-icon"><span data-icon="receipt"></span></span>
            </div>
            <div class="kpi-value">86</div>
            <div class="kpi-meta">
              <span class="kpi-trend up"><span data-icon="trending-up"></span> +12</span>
              este mes
            </div>
          </article>

          <article class="kpi-card">
            <div class="kpi-top">
              <span class="kpi-label">Cartera pendiente</span>
              <span class="kpi-icon warning"><span data-icon="alert-circle"></span></span>
            </div>
            <div class="kpi-value">$1.240.000</div>
            <div class="kpi-meta">6 estudiantes con saldo</div>
          </article>

          <article class="kpi-card">
            <div class="kpi-top">
              <span class="kpi-label">Matrículas del mes</span>
              <span class="kpi-icon violet"><span data-icon="clipboard-check"></span></span>
            </div>
            <div class="kpi-value">$2.880.000</div>
            <div class="kpi-meta">24 matrículas registradas</div>
          </article>
        </section>

        <!-- ========================= PAGOS ========================= -->
        <section class="content-section" id="pagos">
          <div class="section-head">
            <div>
              <h2>Pagos</h2>
              <p>86 movimientos registrados en noviembre · 2 pendientes por validar</p>
            </div>
            <div class="section-actions">
              <button class="btn btn-outline btn-sm" type="button" data-toast="Filtros de pagos (demo).">
                <span data-icon="filter"></span>
                Filtros
              </button>
              <button class="btn btn-primary btn-sm" type="button" data-modal-open="paymentModal">
                <span data-icon="plus"></span>
                Registrar pago
              </button>
            </div>
          </div>

          <div class="card">
            <div class="toolbar">
              <div class="toolbar-search">
                <span data-icon="search"></span>
                <input type="search" placeholder="Buscar por recibo, estudiante o referencia…" aria-label="Buscar pago">
              </div>
              <select class="select" aria-label="Filtrar por mes">
                <option>Noviembre 2026</option>
                <option>Octubre 2026</option>
                <option>Septiembre 2026</option>
              </select>
              <select class="select" aria-label="Filtrar por estado">
                <option>Todos los estados</option>
                <option>Aprobado</option>
                <option>Pendiente</option>
                <option>Rechazado</option>
              </select>
              <select class="select" aria-label="Filtrar por método">
                <option>Todos los métodos</option>
                <option>Transferencia</option>
                <option>Efectivo</option>
                <option>Tarjeta</option>
                <option>PSE</option>
              </select>
            </div>

            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Recibo</th>
                    <th>Estudiante</th>
                    <th>Concepto</th>
                    <th>Fecha</th>
                    <th>Método</th>
                    <th>Valor</th>
                    <th>Estado</th>
                    <th class="text-right">Acciones</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class="num"><strong>P-1042</strong></td>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-teal avatar-sm">VR</span>
                        <div><strong>Valentina Ríos</strong><span>Inglés B1</span></div>
                      </div>
                    </td>
                    <td>Mensualidad nov.</td>
                    <td>05 nov 2026</td>
                    <td>Transferencia</td>
                    <td class="num"><strong>$320.000</strong></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                    <td>
                      <div class="actions">
                        <button class="row-action" type="button" aria-label="Ver recibo" data-toast="Recibo P-1042 (demo)."><span data-icon="eye"></span></button>
                        <button class="row-action" type="button" aria-label="Descargar" data-toast="Descarga de recibo (demo)."><span data-icon="download"></span></button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="num"><strong>P-1041</strong></td>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-violet avatar-sm">AF</span>
                        <div><strong>Andrés F. Castro</strong><span>Francés A2</span></div>
                      </div>
                    </td>
                    <td>Mensualidad nov.</td>
                    <td>05 nov 2026</td>
                    <td>Efectivo</td>
                    <td class="num"><strong>$300.000</strong></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                    <td>
                      <div class="actions">
                        <button class="row-action" type="button" aria-label="Ver recibo" data-toast="Recibo P-1041 (demo)."><span data-icon="eye"></span></button>
                        <button class="row-action" type="button" aria-label="Descargar" data-toast="Descarga de recibo (demo)."><span data-icon="download"></span></button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="num"><strong>P-1040</strong></td>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-warning avatar-sm">MH</span>
                        <div><strong>María José Herrera</strong><span>Francés A1</span></div>
                      </div>
                    </td>
                    <td>Matrícula</td>
                    <td>04 nov 2026</td>
                    <td>Tarjeta</td>
                    <td class="num"><strong>$120.000</strong></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                    <td>
                      <div class="actions">
                        <button class="row-action" type="button" aria-label="Ver recibo" data-toast="Recibo P-1040 (demo)."><span data-icon="eye"></span></button>
                        <button class="row-action" type="button" aria-label="Descargar" data-toast="Descarga de recibo (demo)."><span data-icon="download"></span></button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="num"><strong>P-1039</strong></td>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-danger avatar-sm">LO</span>
                        <div><strong>Luisa F. Ortiz</strong><span>Portugués A1</span></div>
                      </div>
                    </td>
                    <td>Mensualidad nov.</td>
                    <td>03 nov 2026</td>
                    <td>Transferencia</td>
                    <td class="num"><strong>$280.000</strong></td>
                    <td><span class="badge badge-warning">Pendiente</span></td>
                    <td>
                      <div class="actions">
                        <button class="row-action" type="button" aria-label="Validar pago" data-toast="Pago validado correctamente (demo)."><span data-icon="check"></span></button>
                        <button class="row-action" type="button" aria-label="Ver comprobante" data-toast="Comprobante adjunto (demo)."><span data-icon="file-text"></span></button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="num"><strong>P-1038</strong></td>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-success avatar-sm">SV</span>
                        <div><strong>Sebastián Vargas</strong><span>Italiano A1</span></div>
                      </div>
                    </td>
                    <td>Mensualidad nov.</td>
                    <td>02 nov 2026</td>
                    <td>PSE</td>
                    <td class="num"><strong>$280.000</strong></td>
                    <td><span class="badge badge-danger">Rechazado</span></td>
                    <td>
                      <div class="actions">
                        <button class="row-action" type="button" aria-label="Ver detalle" data-toast="Motivo: fondos insuficientes (demo)."><span data-icon="eye"></span></button>
                        <button class="row-action" type="button" aria-label="Reintentar" data-toast="Enlace de pago reenviado (demo)."><span data-icon="rotate-ccw"></span></button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="num"><strong>P-1037</strong></td>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-pink avatar-sm">DS</span>
                        <div><strong>Daniela Sánchez</strong><span>Inglés A1</span></div>
                      </div>
                    </td>
                    <td>Matrícula</td>
                    <td>01 nov 2026</td>
                    <td>Efectivo</td>
                    <td class="num"><strong>$120.000</strong></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                    <td>
                      <div class="actions">
                        <button class="row-action" type="button" aria-label="Ver recibo" data-toast="Recibo P-1037 (demo)."><span data-icon="eye"></span></button>
                        <button class="row-action" type="button" aria-label="Descargar" data-toast="Descarga de recibo (demo)."><span data-icon="download"></span></button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="num"><strong>P-1036</strong></td>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-success avatar-sm">CR</span>
                        <div><strong>Camila Restrepo</strong><span>Inglés C1</span></div>
                      </div>
                    </td>
                    <td>Mensualidad oct.</td>
                    <td>31 oct 2026</td>
                    <td>Transferencia</td>
                    <td class="num"><strong>$340.000</strong></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                    <td>
                      <div class="actions">
                        <button class="row-action" type="button" aria-label="Ver recibo" data-toast="Recibo P-1036 (demo)."><span data-icon="eye"></span></button>
                        <button class="row-action" type="button" aria-label="Descargar" data-toast="Descarga de recibo (demo)."><span data-icon="download"></span></button>
                      </div>
                    </td>
                  </tr>
                  <tr>
                    <td class="num"><strong>P-1035</strong></td>
                    <td>
                      <div class="cell-main">
                        <span class="avatar avatar-dark avatar-sm">JM</span>
                        <div><strong>Juan Pablo Mejía</strong><span>Francés A2</span></div>
                      </div>
                    </td>
                    <td>Certificación</td>
                    <td>30 oct 2026</td>
                    <td>Tarjeta</td>
                    <td class="num"><strong>$80.000</strong></td>
                    <td><span class="badge badge-success">Aprobado</span></td>
                    <td>
                      <div class="actions">
                        <button class="row-action" type="button" aria-label="Ver recibo" data-toast="Recibo P-1035 (demo)."><span data-icon="eye"></span></button>
                        <button class="row-action" type="button" aria-label="Descargar" data-toast="Descarga de recibo (demo)."><span data-icon="download"></span></button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>

            <div class="table-footer">
              <span>Mostrando 1–8 de 86 pagos</span>
              <div class="pagination" aria-label="Paginación">
                <a href="#" aria-label="Página anterior"><span data-icon="chevron-left"></span></a>
                <span class="is-active">1</span>
                <a href="#">2</a>
                <a href="#">3</a>
                <a href="#">…</a>
                <a href="#">11</a>
                <a href="#" aria-label="Página siguiente"><span data-icon="chevron-right"></span></a>
              </div>
            </div>
          </div>
        </section>

        <!-- ========================= CARTERA ========================= -->
        <section class="content-section" id="cartera">
          <div class="section-head">
            <div>
              <h2>Cartera y comprobantes</h2>
              <p>6 estudiantes con saldo pendiente · 2 comprobantes por validar</p>
            </div>
            <div class="section-actions">
              <button class="btn btn-outline btn-sm" type="button" data-toast="Recordatorios enviados a 6 estudiantes (demo).">
                <span data-icon="send"></span>
                Enviar recordatorios
              </button>
            </div>
          </div>

          <div class="finance-grid">
            <div class="card">
              <div class="card-header">
                <div>
                  <h3>Cartera por estudiante</h3>
                  <p>Saldos pendientes al 5 de noviembre</p>
                </div>
                <span class="badge badge-warning">$1.240.000</span>
              </div>
              <div class="card-body">
                <div class="debtor-list">
                  <div class="debtor">
                    <span class="avatar avatar-violet">AF</span>
                    <div class="debtor-info">
                      <strong>Andrés Felipe Castro</strong>
                      <span>Francés A2 · vence 10 nov</span>
                    </div>
                    <div class="debtor-amount">
                      <strong>$300.000</strong>
                      <span>1 cuota</span>
                    </div>
                    <button class="row-action" type="button" aria-label="Enviar recordatorio" data-toast="Recordatorio enviado a Andrés Felipe (demo)."><span data-icon="bell"></span></button>
                  </div>

                  <div class="debtor">
                    <span class="avatar avatar-danger">LO</span>
                    <div class="debtor-info">
                      <strong>Luisa Fernanda Ortiz</strong>
                      <span>Portugués A1 · vence 8 nov</span>
                    </div>
                    <div class="debtor-amount">
                      <strong>$280.000</strong>
                      <span>1 cuota</span>
                    </div>
                    <button class="row-action" type="button" aria-label="Enviar recordatorio" data-toast="Recordatorio enviado a Luisa Fernanda (demo)."><span data-icon="bell"></span></button>
                  </div>

                  <div class="debtor">
                    <span class="avatar avatar-success">SV</span>
                    <div class="debtor-info">
                      <strong>Sebastián Vargas</strong>
                      <span>Italiano A1 · vence 12 nov</span>
                    </div>
                    <div class="debtor-amount">
                      <strong>$280.000</strong>
                      <span>1 cuota</span>
                    </div>
                    <button class="row-action" type="button" aria-label="Enviar recordatorio" data-toast="Recordatorio enviado a Sebastián (demo)."><span data-icon="bell"></span></button>
                  </div>

                  <div class="debtor">
                    <span class="avatar avatar-pink">DS</span>
                    <div class="debtor-info">
                      <strong>Daniela Sánchez</strong>
                      <span>Inglés A1 · vence 15 nov</span>
                    </div>
                    <div class="debtor-amount">
                      <strong>$160.000</strong>
                      <span>Saldo parcial</span>
                    </div>
                    <button class="row-action" type="button" aria-label="Enviar recordatorio" data-toast="Recordatorio enviado a Daniela (demo)."><span data-icon="bell"></span></button>
                  </div>

                  <div class="debtor">
                    <span class="avatar avatar-dark">JM</span>
                    <div class="debtor-info">
                      <strong>Juan Pablo Mejía</strong>
                      <span>Francés A2 · vence 18 nov</span>
                    </div>
                    <div class="debtor-amount">
                      <strong>$140.000</strong>
                      <span>Saldo parcial</span>
                    </div>
                    <button class="row-action" type="button" aria-label="Enviar recordatorio" data-toast="Recordatorio enviado a Juan Pablo (demo)."><span data-icon="bell"></span></button>
                  </div>

                  <div class="debtor">
                    <span class="avatar avatar-warning">MH</span>
                    <div class="debtor-info">
                      <strong>María José Herrera</strong>
                      <span>Francés A1 · vence 20 nov</span>
                    </div>
                    <div class="debtor-amount">
                      <strong>$80.000</strong>
                      <span>Saldo parcial</span>
                    </div>
                    <button class="row-action" type="button" aria-label="Enviar recordatorio" data-toast="Recordatorio enviado a María José (demo)."><span data-icon="bell"></span></button>
                  </div>
                </div>
              </div>
              <div class="card-footer">
                <div class="flex-between">
                  <span class="text-sm text-muted">Total en cartera</span>
                  <strong class="text-lg text-ink">$1.240.000</strong>
                </div>
              </div>
            </div>

            <div class="card">
              <div class="card-header">
                <div>
                  <h3>Comprobantes por validar</h3>
                  <p>Pagos reportados por estudiantes</p>
                </div>
                <span class="badge badge-warning">2</span>
              </div>
              <div class="card-body">
                <div class="receipt-list">
                  <div class="receipt">
                    <span class="flag flag-en"></span>
                    <div class="receipt-info">
                      <strong>Valentina Ríos</strong>
                      <span>$320.000 · 05 nov · Ref. 8842</span>
                    </div>
                    <div class="receipt-actions">
                      <button class="ok" type="button" aria-label="Aprobar comprobante" data-toast="Comprobante aprobado y saldo actualizado (demo)."><span data-icon="check"></span></button>
                      <button class="no" type="button" aria-label="Rechazar comprobante" data-toast="Comprobante rechazado (demo)."><span data-icon="x"></span></button>
                    </div>
                  </div>

                  <div class="receipt">
                    <span class="flag flag-pt"></span>
                    <div class="receipt-info">
                      <strong>Luisa Fernanda Ortiz</strong>
                      <span>$280.000 · 03 nov · Ref. 7719</span>
                    </div>
                    <div class="receipt-actions">
                      <button class="ok" type="button" aria-label="Aprobar comprobante" data-toast="Comprobante aprobado y saldo actualizado (demo)."><span data-icon="check"></span></button>
                      <button class="no" type="button" aria-label="Rechazar comprobante" data-toast="Comprobante rechazado (demo)."><span data-icon="x"></span></button>
                    </div>
                  </div>

                  <div class="receipt">
                    <span class="flag flag-fr"></span>
                    <div class="receipt-info">
                      <strong>María José Herrera</strong>
                      <span>$120.000 · 02 nov · Ref. 6604</span>
                    </div>
                    <div class="receipt-actions">
                      <button class="ok" type="button" aria-label="Aprobar comprobante" data-toast="Comprobante aprobado y saldo actualizado (demo)."><span data-icon="check"></span></button>
                      <button class="no" type="button" aria-label="Rechazar comprobante" data-toast="Comprobante rechazado (demo)."><span data-icon="x"></span></button>
                    </div>
                  </div>
                </div>

                <div class="notice notice-primary mt-16">
                  <span data-icon="info"></span>
                  <div>Los pagos se validan con el comprobante o recibo cargado por el estudiante. No hay pasarela automática en esta versión.</div>
                </div>
              </div>
            </div>
          </div>
        </section>

        <!-- ========================= REPORTES ========================= -->
        <section class="content-section" id="reportes">
          <div class="section-head">
            <div>
              <h2>Reportes financieros</h2>
              <p>Consolidado de ingresos y matrículas · periodo 2026</p>
            </div>
            <div class="section-actions">
              <select class="select" aria-label="Seleccionar año" style="width:auto;height:36px;font-size:13px">
                <option>2026</option>
                <option>2025</option>
              </select>
              <button class="btn btn-primary btn-sm" type="button" data-toast="Reporte financiero exportado (demo).">
                <span data-icon="download"></span>
                Exportar reporte
              </button>
            </div>
          </div>

          <div class="report-grid">
            <article class="card">
              <div class="card-body">
                <div class="chart-head">
                  <div>
                    <h3>Ingresos por mes</h3>
                    <p>Recaudo total · 2026</p>
                  </div>
                  <div class="chart-total">
                    <strong>$18.640.000</strong>
                    <span>Noviembre</span>
                    <div class="kpi-trend up"><span data-icon="trending-up"></span> 8.4% vs. octubre</div>
                  </div>
                </div>

                <div class="bar-chart bar-chart-sm" role="img" aria-label="Ingresos mensuales de abril a noviembre de 2026">
                  <div class="bar-col"><div class="bar" style="height:62%"><span class="bar-value">$12.4M</span></div><span class="bar-label">Abr</span></div>
                  <div class="bar-col"><div class="bar" style="height:68%"><span class="bar-value">$13.1M</span></div><span class="bar-label">May</span></div>
                  <div class="bar-col"><div class="bar" style="height:72%"><span class="bar-value">$13.8M</span></div><span class="bar-label">Jun</span></div>
                  <div class="bar-col"><div class="bar" style="height:75%"><span class="bar-value">$14.2M</span></div><span class="bar-label">Jul</span></div>
                  <div class="bar-col"><div class="bar" style="height:82%"><span class="bar-value">$15.6M</span></div><span class="bar-label">Ago</span></div>
                  <div class="bar-col"><div class="bar" style="height:86%"><span class="bar-value">$16.2M</span></div><span class="bar-label">Sep</span></div>
                  <div class="bar-col"><div class="bar" style="height:91%"><span class="bar-value">$17.2M</span></div><span class="bar-label">Oct</span></div>
                  <div class="bar-col is-current"><div class="bar" style="height:100%"><span class="bar-value">$18.64M</span></div><span class="bar-label">Nov</span></div>
                </div>
              </div>
            </article>

            <article class="card">
              <div class="card-header">
                <div>
                  <h3>Ingresos por idioma</h3>
                  <p>Participación sobre el recaudo total</p>
                </div>
              </div>
              <div class="card-body">
                <div class="donut-wrap">
                  <div class="donut" role="img" aria-label="Inglés 62%, francés 18%, portugués 12%, italiano 8%">
                    <div class="donut-center">
                      <strong>62%</strong>
                      <span>Inglés</span>
                    </div>
                  </div>
                  <div class="donut-legend">
                    <div class="legend-item">
                      <span class="legend-dot" style="background:#0284C7"></span>
                      <span>Inglés</span>
                      <strong>$11.56M</strong>
                    </div>
                    <div class="legend-item">
                      <span class="legend-dot" style="background:#7C3AED"></span>
                      <span>Francés</span>
                      <strong>$3.35M</strong>
                    </div>
                    <div class="legend-item">
                      <span class="legend-dot" style="background:#F59E0B"></span>
                      <span>Portugués</span>
                      <strong>$2.24M</strong>
                    </div>
                    <div class="legend-item">
                      <span class="legend-dot" style="background:#10B981"></span>
                      <span>Italiano</span>
                      <strong>$1.49M</strong>
                    </div>
                  </div>
                </div>
              </div>
            </article>
          </div>

          <div class="card">
            <div class="card-header">
              <div>
                <h3>Consolidado mensual</h3>
                <p>Detalle de ingresos por concepto</p>
              </div>
              <span class="badge badge-success">Actualizado hoy</span>
            </div>
            <div class="table-wrap">
              <table class="table">
                <thead>
                  <tr>
                    <th>Mes</th>
                    <th>Matrículas</th>
                    <th>Mensualidades</th>
                    <th>Otros ingresos</th>
                    <th>Total</th>
                    <th>Variación</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td><strong>Noviembre 2026</strong></td>
                    <td class="num">$2.880.000</td>
                    <td class="num">$14.960.000</td>
                    <td class="num">$800.000</td>
                    <td class="num"><strong>$18.640.000</strong></td>
                    <td><span class="kpi-trend up"><span data-icon="trending-up"></span> 8.4%</span></td>
                  </tr>
                  <tr>
                    <td><strong>Octubre 2026</strong></td>
                    <td class="num">$2.640.000</td>
                    <td class="num">$13.800.000</td>
                    <td class="num">$760.000</td>
                    <td class="num"><strong>$17.200.000</strong></td>
                    <td><span class="kpi-trend up"><span data-icon="trending-up"></span> 5.8%</span></td>
                  </tr>
                  <tr>
                    <td><strong>Septiembre 2026</strong></td>
                    <td class="num">$2.400.000</td>
                    <td class="num">$13.080.000</td>
                    <td class="num">$720.000</td>
                    <td class="num"><strong>$16.200.000</strong></td>
                    <td><span class="kpi-trend up"><span data-icon="trending-up"></span> 3.8%</span></td>
                  </tr>
                  <tr>
                    <td><strong>Agosto 2026</strong></td>
                    <td class="num">$2.280.000</td>
                    <td class="num">$12.600.000</td>
                    <td class="num">$720.000</td>
                    <td class="num"><strong>$15.600.000</strong></td>
                    <td><span class="kpi-trend up"><span data-icon="trending-up"></span> 9.9%</span></td>
                  </tr>
                  <tr>
                    <td><strong>Julio 2026</strong></td>
                    <td class="num">$2.160.000</td>
                    <td class="num">$11.400.000</td>
                    <td class="num">$640.000</td>
                    <td class="num"><strong>$14.200.000</strong></td>
                    <td><span class="kpi-trend up"><span data-icon="trending-up"></span> 2.9%</span></td>
                  </tr>
                  <tr>
                    <td><strong>Junio 2026</strong></td>
                    <td class="num">$2.040.000</td>
                    <td class="num">$11.160.000</td>
                    <td class="num">$600.000</td>
                    <td class="num"><strong>$13.800.000</strong></td>
                    <td><span class="kpi-trend down"><span data-icon="trending-down"></span> 5.3%</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="table-footer">
              <span class="report-note" style="margin-top:0;padding-top:0;border-top:0">
                <span data-icon="check-circle"></span>
                Reporte generado automáticamente el 5 de noviembre de 2026 · 9:42 a. m.
              </span>
              <button class="btn btn-outline btn-sm" type="button" data-toast="Consolidado descargado (demo).">
                <span data-icon="download"></span>
                Descargar
              </button>
            </div>
          </div>
        </section>
      </main>
    </div>
  </div>

  <!-- Registrar pago (demo) -->
  <div class="modal-backdrop" id="paymentModal" data-success="Pago registrado correctamente (demo).">
    <div class="modal" role="dialog" aria-modal="true" aria-labelledby="paymentTitle">
      <div class="modal-head">
        <div>
          <h3 id="paymentTitle">Registrar pago</h3>
          <p>Vista previa del formulario de recaudo.</p>
        </div>
        <button class="modal-close" type="button" data-modal-close aria-label="Cerrar"><span data-icon="x"></span></button>
      </div>

      <form>
        <div class="modal-body">
          <div class="form-grid">
            <div class="field col-span-2">
              <label class="label" for="payStudent">Estudiante</label>
              <select class="select" id="payStudent" required>
                <option value="">Selecciona un estudiante</option>
                <option>Valentina Ríos · Inglés B1</option>
                <option>Andrés Felipe Castro · Francés A2</option>
                <option>Luisa Fernanda Ortiz · Portugués A1</option>
                <option>Sebastián Vargas · Italiano A1</option>
              </select>
            </div>
            <div class="field">
              <label class="label" for="payConcept">Concepto</label>
              <select class="select" id="payConcept" required>
                <option>Mensualidad</option>
                <option>Matrícula</option>
                <option>Certificación</option>
                <option>Otro</option>
              </select>
            </div>
            <div class="field">
              <label class="label" for="payAmount">Monto (COP)</label>
              <input class="input" id="payAmount" type="number" placeholder="320000" min="0" required>
            </div>
            <div class="field">
              <label class="label" for="payMethod">Método de pago</label>
              <select class="select" id="payMethod" required>
                <option>Transferencia</option>
                <option>Efectivo</option>
                <option>Tarjeta</option>
                <option>PSE</option>
              </select>
            </div>
            <div class="field">
              <label class="label" for="payDate">Fecha</label>
              <input class="input" id="payDate" type="date" value="2026-11-05" required>
            </div>
            <div class="field col-span-2">
              <label class="label" for="payRef">Referencia o comprobante</label>
              <input class="input" id="payRef" type="text" placeholder="Número de transacción, recibo o soporte">
            </div>
          </div>

          <div class="notice notice-primary mt-16">
            <span data-icon="info"></span>
            <div>El saldo del estudiante y el consolidado mensual se actualizan automáticamente al guardar.</div>
          </div>
        </div>

        <div class="modal-foot">
          <button class="btn btn-ghost" type="button" data-modal-close>Cancelar</button>
          <button class="btn btn-primary" type="submit"><span data-icon="check"></span> Guardar pago</button>
        </div>
      </form>
    </div>
  </div>

  <script src="js/shell.js?v=3"></script>
  <script src="js/main.js?v=3"></script>
</body>
</html>
