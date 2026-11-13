/* ============================================================
   Shared icon set (inline SVG, no external library)
   ============================================================ */
(function () {
  'use strict';

  var ICONS = {
    grid: '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    home: '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>',
    book: '<path d="M12 7v14"/><path d="M3 18a1 1 0 0 1-1-1V4a1 1 0 0 1 1-1h5a4 4 0 0 1 4 4 4 4 0 0 1 4-4h5a1 1 0 0 1 1 1v13a1 1 0 0 1-1 1h-6a3 3 0 0 0-3 3 3 3 0 0 0-3-3z"/>',
    wallet: '<path d="M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4h-3a2 2 0 0 0 0 4h3a1 1 0 0 0 1-1v-2a1 1 0 0 0-1-1"/><path d="M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4"/>',
    users: '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    user: '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>',
    'user-check': '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m16 11 2 2 4-4"/>',
    'graduation-cap': '<path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/><path d="M22 10v6"/>',
    presentation: '<path d="M2 3h20"/><path d="M21 3v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V3"/><path d="m7 21 5-5 5 5"/>',
    bell: '<path d="M10.3 21a1.9 1.9 0 0 0 3.4 0"/><path d="M18 8a6 6 0 0 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>',
    search: '<circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>',
    'chevron-down': '<path d="m6 9 6 6 6-6"/>',
    'chevron-right': '<path d="m9 18 6-6-6-6"/>',
    'chevron-left': '<path d="m15 18-6-6 6-6"/>',
    'chevron-up': '<path d="m18 15-6-6-6 6"/>',
    menu: '<path d="M4 6h16M4 12h16M4 18h16"/>',
    x: '<path d="M18 6 6 18M6 6l12 12"/>',
    logout: '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>',
    settings: '<path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/>',
    help: '<circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/>',
    calendar: '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>',
    'calendar-check': '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/><path d="m9 16 2 2 4-4"/>',
    clock: '<circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/>',
    'map-pin': '<path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/>',
    check: '<path d="M20 6 9 17l-5-5"/>',
    'check-circle': '<path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m22 4-10 10.01-3-3"/>',
    'alert-triangle': '<path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/><path d="M12 9v4M12 17h.01"/>',
    'alert-circle': '<circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/>',
    info: '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>',
    'trending-up': '<path d="m22 7-8.5 8.5-5-5L2 17"/><path d="M16 7h6v6"/>',
    'trending-down': '<path d="m22 17-8.5-8.5-5 5L2 7"/><path d="M16 17h6v-6"/>',
    plus: '<path d="M12 5v14M5 12h14"/>',
    download: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
    upload: '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m17 8-5-5-5 5"/><path d="M12 3v12"/>',
    filter: '<path d="M22 3H2l8 9.46V19l4 2v-8.54L22 3z"/>',
    edit: '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
    eye: '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/>',
    'eye-off': '<path d="m2 2 20 20"/><path d="M6.7 6.7C3.9 8.3 2 12 2 12s3.5 7 10 7c1.9 0 3.6-.6 5-1.4"/><path d="M10.6 5.2A10.9 10.9 0 0 1 12 5c6.5 0 10 7 10 7a17.8 17.8 0 0 1-2.4 3.4"/><path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"/>',
    'more-horizontal': '<circle cx="5" cy="12" r="1"/><circle cx="12" cy="12" r="1"/><circle cx="19" cy="12" r="1"/>',
    'arrow-right': '<path d="M5 12h14"/><path d="m12 5 7 7-7 7"/>',
    'arrow-left': '<path d="M19 12H5"/><path d="m12 19-7-7 7-7"/>',
    'arrow-up-right': '<path d="M7 17 17 7"/><path d="M7 7h10v10"/>',
    'external-link': '<path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>',
    'credit-card': '<rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/>',
    dollar: '<path d="M12 2v20"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>',
    'file-text': '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="M16 13H8M16 17H8M10 9H8"/>',
    'file-check': '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6"/><path d="m9 15 2 2 4-4"/>',
    'bar-chart': '<path d="M3 3v18h18"/><rect x="7" y="10" width="3" height="7" rx="1"/><rect x="12" y="6" width="3" height="11" rx="1"/><rect x="17" y="13" width="3" height="4" rx="1"/>',
    'pie-chart': '<path d="M21.21 15.89A10 10 0 1 1 8 2.83"/><path d="M22 12A10 10 0 0 0 12 2v10z"/>',
    'clipboard-check': '<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>',
    'clipboard-list': '<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="M12 11h4M12 16h4M8 11h.01M8 16h.01"/>',
    star: '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1L12 2Z"/>',
    globe: '<circle cx="12" cy="12" r="10"/><path d="M2 12h20"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
    mail: '<rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/>',
    phone: '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.8 19.8 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>',
    'message-circle': '<path d="M21 11.5a8.4 8.4 0 0 1-9 8.4 8.5 8.5 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.4 8.4 0 0 1 8.4-8.4 8.4 8.4 0 0 1 8.6 8.4z"/>',
    lock: '<rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
    shield: '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10Z"/>',
    award: '<circle cx="12" cy="8" r="6"/><path d="M15.5 13.5 17 22l-5-3-5 3 1.5-8.5"/>',
    receipt: '<path d="M4 2v20l3-2 3 2 3-2 3 2 3-2V2l-3 2-3-2-3 2-3-2Z"/><path d="M8 7h8M8 11h8M8 15h5"/>',
    banknote: '<rect x="2" y="6" width="20" height="12" rx="2"/><circle cx="12" cy="12" r="2"/><path d="M6 12h.01M18 12h.01"/>',
    sparkles: '<path d="m12 3 1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9L12 3Z"/><path d="M5 19v2M4 20h2M19 3v2M18 4h2"/>',
    target: '<circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2"/>',
    layers: '<path d="m12 2 9 5-9 5-9-5 9-5Z"/><path d="m3 12 9 5 9-5"/><path d="m3 17 9 5 9-5"/>',
    quote: '<path d="M3 21c3 0 7-1 7-8V5c0-1.25-.75-2-2-2H4c-1.25 0-2 .75-2 2v6c0 1.25.75 2 2 2h3c0 4-2 5-4 5Z"/><path d="M15 21c3 0 7-1 7-8V5c0-1.25-.75-2-2-2h-4c-1.25 0-2 .75-2 2v6c0 1.25.75 2 2 2h3c0 4-2 5-4 5Z"/>',
    percent: '<path d="M19 5 5 19"/><circle cx="6.5" cy="6.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/>',
    facebook: '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
    instagram: '<rect x="2" y="2" width="20" height="20" rx="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37Z"/><path d="M17.5 6.5h.01"/>',
    linkedin: '<path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-4 0v7h-4v-7a6 6 0 0 1 6-6Z"/><rect x="2" y="9" width="4" height="12"/><circle cx="4" cy="4" r="2"/>',
    'book-open': '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>',
    'panel-left': '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18"/>',
    send: '<path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/>',
    'rotate-ccw': '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/>',
    trash: '<path d="M3 6h18"/><path d="M8 6V4h8v2"/><path d="M19 6l-1 14H6L5 6"/><path d="M10 11v6M14 11v6"/>'
  };

  function icon(name, className) {
    var body = ICONS[name] || ICONS.info;
    return '<svg class="' + (className || '') + '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' + body + '</svg>';
  }

  window.AppIcon = icon;

  /* ============================================================
     Authenticated app shell
     ============================================================ */
  var role = document.body.getAttribute('data-role');
  if (!role) return;

  var page = document.body.getAttribute('data-page') || 'dashboard';
  var title = document.body.getAttribute('data-title') || 'Panel';
  var subtitle = document.body.getAttribute('data-subtitle') || '';

  var USERS = {
    admin: {
      name: 'Administrador',
      role: 'Administrador',
      initials: 'AD',
      avatar: 'avatar-warning'
    },
    student: {
      name: 'Estudiante',
      role: 'Estudiante',
      initials: 'ES',
      avatar: 'avatar-teal'
    },
    teacher: {
      name: 'Profesor',
      role: 'Profesor',
      initials: 'PR',
      avatar: 'avatar-violet'
    }
  };

  function escapeHtml(value) {
    return String(value == null ? '' : value)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  var NAVS = {
    admin: [
      {
        label: 'Principal',
        items: [
          { id: 'dashboard', href: 'admin.php', icon: 'grid', text: 'Dashboard' }
        ]
      },
      {
        label: 'Gestión',
        items: [
          { id: 'academico', href: 'academico.php', icon: 'book', text: 'Gestión académica' },
          { id: 'cursos', href: 'cursos.php', icon: 'book-open', text: 'Cursos' },
          { id: 'usuarios', href: 'admin.php#usuarios', icon: 'users', text: 'Usuarios' },
          { id: 'finanzas', href: 'finanzas.php', icon: 'wallet', text: 'Finanzas y reportes', badge: '6', badgeClass: 'warn' }
        ]
      },
      {
        label: 'Portales',
        items: [
          { id: 'estudiante', href: 'estudiante.php', icon: 'graduation-cap', text: 'Portal del estudiante' },
          { id: 'profesor', href: 'profesor.php', icon: 'presentation', text: 'Portal del profesor' }
        ]
      },
      {
        label: 'General',
        items: [
          { id: 'sitio', href: 'index.html', icon: 'globe', text: 'Ver sitio web' }
        ]
      }
    ],
    student: [
      {
        label: 'Mi espacio',
        items: [
          { id: 'inicio', href: '#inicio', icon: 'home', text: 'Inicio' },
          { id: 'cursos', href: '#cursos', icon: 'book', text: 'Mis cursos' },
          { id: 'horario', href: '#horario', icon: 'calendar', text: 'Mi horario' },
          { id: 'notas', href: '#notas', icon: 'file-text', text: 'Mis notas' },
          { id: 'pagos', href: '#pagos', icon: 'wallet', text: 'Mis pagos' },
          { id: 'perfil', href: '#perfil', icon: 'user', text: 'Mi perfil' }
        ]
      },
      {
        label: 'General',
        items: [
          { id: 'sitio', href: 'index.html', icon: 'globe', text: 'Ver sitio web' }
        ]
      }
    ],
    teacher: [
      {
        label: 'Mi espacio',
        items: [
          { id: 'inicio', href: '#inicio', icon: 'home', text: 'Inicio' },
          { id: 'grupos', href: '#grupos', icon: 'users', text: 'Mis grupos' },
          { id: 'asistencia', href: '#asistencia', icon: 'user-check', text: 'Asistencia' },
          { id: 'notas', href: '#notas', icon: 'file-text', text: 'Notas' }
        ]
      },
      {
        label: 'General',
        items: [
          { id: 'sitio', href: 'index.html', icon: 'globe', text: 'Ver sitio web' }
        ]
      }
    ]
  };

  var NOTIFS = {
    admin: [
      { icon: 'alert-triangle', tone: 'warning', title: 'Inglés B2 alcanzó el cupo máximo', text: '18 de 18 estudiantes matriculados.', time: 'Hace 12 minutos' },
      { icon: 'banknote', tone: 'success', title: 'Nuevo pago registrado', text: 'Valentina Ríos · $320.000 · Mensualidad.', time: 'Hace 38 minutos' },
      { icon: 'file-check', tone: 'primary', title: '2 comprobantes por validar', text: 'Pagos reportados por estudiantes.', time: 'Hoy, 9:15 a. m.' }
    ],
    student: [
      { icon: 'award', tone: 'success', title: 'Nueva nota publicada', text: 'Parcial 3 de Inglés B1: 4.1', time: 'Hace 2 horas' },
      { icon: 'calendar', tone: 'primary', title: 'Recordatorio de clase', text: 'Inglés B1 · martes 6:00 p. m. · Salón 204', time: 'Hoy, 8:00 a. m.' },
      { icon: 'check-circle', tone: 'success', title: 'Pago confirmado', text: 'Mensualidad de noviembre · $320.000', time: 'Ayer' }
    ],
    teacher: [
      { icon: 'file-text', tone: 'warning', title: '12 notas pendientes', text: 'Inglés B1 · Tercer parcial.', time: 'Hace 1 hora' },
      { icon: 'user-check', tone: 'primary', title: 'Asistencia por registrar', text: 'Inglés A1 · Clase de hoy 8:00 p. m.', time: 'Hoy, 6:40 p. m.' },
      { icon: 'users', tone: 'success', title: 'Nuevo estudiante en tu grupo', text: 'Daniela Sánchez · Inglés A1.', time: 'Ayer' }
    ]
  };

  var defaultUser = USERS[role] || USERS.admin;
  var user = {
    name: document.body.getAttribute('data-user-name') || defaultUser.name,
    role: document.body.getAttribute('data-user-role') || defaultUser.role,
    initials: document.body.getAttribute('data-user-initials') || defaultUser.initials,
    avatar: defaultUser.avatar
  };
  var nav = NAVS[role] || NAVS.admin;
  var notifs = NOTIFS[role] || NOTIFS.admin;

  function renderNav() {
    return nav.map(function (group) {
      return '<div class="nav-group">' +
        '<div class="nav-label">' + group.label + '</div>' +
        group.items.map(function (item) {
          var isActive = item.id === page;
          var badge = item.badge ? '<span class="nav-badge ' + (item.badgeClass || '') + '">' + item.badge + '</span>' : '';
          return '<a class="nav-item' + (isActive ? ' is-active' : '') + '" href="' + item.href + '">' +
            icon(item.icon) +
            '<span>' + item.text + '</span>' +
            badge +
            '</a>';
        }).join('') +
        '</div>';
    }).join('');
  }

  var sidebar = document.getElementById('sidebar');
  if (sidebar) {
    sidebar.innerHTML =
      '<div class="sidebar-brand">' +
        '<span class="brand-mark">A</span>' +
        '<div>' +
          '<span class="brand-name">Academia de Idiomas</span>' +
          '<span class="brand-sub">Sistema académico</span>' +
        '</div>' +
      '</div>' +
      '<nav class="sidebar-nav" aria-label="Navegación principal">' + renderNav() + '</nav>' +
      '<div class="sidebar-foot">' +
        '<div class="sidebar-user">' +
          '<span class="avatar ' + user.avatar + '">' + escapeHtml(user.initials) + '</span>' +
          '<div class="sidebar-user-info">' +
            '<strong>' + escapeHtml(user.name) + '</strong>' +
            '<span>' + escapeHtml(user.role) + '</span>' +
          '</div>' +
          '<a class="sidebar-logout" href="logout.php" aria-label="Cerrar sesión" title="Cerrar sesión">' + icon('logout') + '</a>' +
        '</div>' +
      '</div>';
  }

  var topbar = document.getElementById('topbar');
  if (topbar) {
    var notifItems = notifs.map(function (n) {
      return '<a class="notif-item" href="#">' +
        '<span class="icon-circle ' + (n.tone === 'primary' ? '' : n.tone) + '">' + icon(n.icon) + '</span>' +
        '<span>' +
          '<strong>' + n.title + '</strong>' +
          '<span>' + n.text + '</span>' +
          '<time>' + n.time + '</time>' +
        '</span>' +
      '</a>';
    }).join('');

    var menuLinks = role === 'admin'
      ? '<a class="dropdown-item" href="admin.php">' + icon('grid') + 'Ir al dashboard</a>' +
        '<a class="dropdown-item" href="index.html">' + icon('globe') + 'Ver sitio web</a>'
      : role === 'student'
        ? '<a class="dropdown-item" href="#perfil">' + icon('user') + 'Mi perfil</a>' +
          '<a class="dropdown-item" href="#pagos">' + icon('wallet') + 'Mis pagos</a>'
        : '<a class="dropdown-item" href="#grupos">' + icon('users') + 'Mis grupos</a>' +
          '<a class="dropdown-item" href="#asistencia">' + icon('user-check') + 'Registrar asistencia</a>';

    topbar.innerHTML =
      '<button class="topbar-icon menu-toggle" id="menuToggle" aria-label="Abrir menú" aria-expanded="false">' + icon('menu') + '</button>' +
      '<div class="topbar-title">' +
        '<h1>' + title + '</h1>' +
        '<p>' + subtitle + '</p>' +
      '</div>' +
      '<div class="topbar-actions">' +
        '<label class="topbar-search">' +
          icon('search') +
          '<input type="search" placeholder="Buscar estudiante, curso o pago…" aria-label="Buscar">' +
        '</label>' +
        '<div class="user-menu" id="notifMenu">' +
          '<button class="topbar-icon" id="notifBtn" aria-label="Notificaciones" aria-expanded="false">' +
            icon('bell') + '<span class="dot-alert"></span>' +
          '</button>' +
          '<div class="dropdown" id="notifDropdown" role="menu">' +
            '<div class="dropdown-title">Notificaciones</div>' +
            '<div class="notif-list">' + notifItems + '</div>' +
            '<a class="dropdown-foot" href="#">Ver todas las notificaciones</a>' +
          '</div>' +
        '</div>' +
        '<div class="user-menu" id="userMenu">' +
          '<button class="user-btn" id="userBtn" aria-label="Menú de usuario" aria-expanded="false">' +
            '<span class="avatar ' + user.avatar + '">' + escapeHtml(user.initials) + '</span>' +
            '<span class="user-btn-text"><strong>' + escapeHtml(user.name.split(' ').slice(0, 2).join(' ')) + '</strong><span>' + escapeHtml(user.role) + '</span></span>' +
            icon('chevron-down') +
          '</button>' +
          '<div class="dropdown" id="userDropdown" role="menu">' +
            '<div class="dropdown-user">' +
              '<span class="avatar ' + user.avatar + '">' + escapeHtml(user.initials) + '</span>' +
              '<div><strong>' + escapeHtml(user.name) + '</strong><span>' + escapeHtml(user.role) + '</span></div>' +
            '</div>' +
            menuLinks +
            '<div class="dropdown-sep"></div>' +
            '<a class="dropdown-item" href="#">' + icon('help') + 'Centro de ayuda</a>' +
            '<a class="dropdown-item danger" href="logout.php">' + icon('logout') + 'Cerrar sesión</a>' +
          '</div>' +
        '</div>' +
      '</div>';
  }

  /* Mobile sidebar */
  var overlay = document.createElement('div');
  overlay.className = 'sidebar-overlay';
  document.body.appendChild(overlay);

  function closeSidebar() {
    if (!sidebar) return;
    sidebar.classList.remove('is-open');
    overlay.classList.remove('is-open');
    var toggle = document.getElementById('menuToggle');
    if (toggle) toggle.setAttribute('aria-expanded', 'false');
    document.body.style.overflow = '';
  }

  var menuToggle = document.getElementById('menuToggle');
  if (menuToggle && sidebar) {
    menuToggle.addEventListener('click', function (event) {
      event.stopPropagation();
      var open = sidebar.classList.toggle('is-open');
      overlay.classList.toggle('is-open', open);
      menuToggle.setAttribute('aria-expanded', String(open));
      document.body.style.overflow = open ? 'hidden' : '';
    });
  }

  overlay.addEventListener('click', closeSidebar);

  if (sidebar) {
    sidebar.querySelectorAll('.nav-item').forEach(function (link) {
      link.addEventListener('click', function () {
        if (window.innerWidth <= 980) closeSidebar();
      });
    });
  }

  /* Dropdowns */
  function setupDropdown(buttonId, dropdownId, menuId) {
    var button = document.getElementById(buttonId);
    var dropdown = document.getElementById(dropdownId);
    var menu = document.getElementById(menuId);
    if (!button || !dropdown || !menu) return;

    button.addEventListener('click', function (event) {
      event.stopPropagation();
      var willOpen = !dropdown.classList.contains('is-open');
      document.querySelectorAll('.dropdown.is-open').forEach(function (d) { d.classList.remove('is-open'); });
      document.querySelectorAll('.user-menu.is-open').forEach(function (m) { m.classList.remove('is-open'); });
      document.querySelectorAll('[aria-expanded="true"]').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
      if (willOpen) {
        dropdown.classList.add('is-open');
        menu.classList.add('is-open');
        button.setAttribute('aria-expanded', 'true');
      }
    });

    dropdown.addEventListener('click', function (event) { event.stopPropagation(); });
  }

  setupDropdown('notifBtn', 'notifDropdown', 'notifMenu');
  setupDropdown('userBtn', 'userDropdown', 'userMenu');

  document.addEventListener('click', function () {
    document.querySelectorAll('.dropdown.is-open').forEach(function (d) { d.classList.remove('is-open'); });
    document.querySelectorAll('.user-menu.is-open').forEach(function (m) { m.classList.remove('is-open'); });
    document.querySelectorAll('[aria-expanded="true"]').forEach(function (b) { b.setAttribute('aria-expanded', 'false'); });
  });

  document.addEventListener('keydown', function (event) {
    if (event.key === 'Escape') {
      closeSidebar();
      document.querySelectorAll('.dropdown.is-open').forEach(function (d) { d.classList.remove('is-open'); });
      document.querySelectorAll('.user-menu.is-open').forEach(function (m) { m.classList.remove('is-open'); });
    }
  });

  /* Scrollspy for single-page portals and anchor navigation */
  var spyLinks = Array.prototype.slice.call(document.querySelectorAll('.sidebar .nav-item[href^="#"], .anchor-nav a[href^="#"]'));
  if (spyLinks.length) {
    var targets = spyLinks.map(function (link) {
      var id = link.getAttribute('href').slice(1);
      return document.getElementById(id);
    }).filter(Boolean);

    if ('IntersectionObserver' in window && targets.length) {
      var observer = new IntersectionObserver(function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          var id = entry.target.id;
          spyLinks.forEach(function (link) {
            link.classList.toggle('is-active', link.getAttribute('href') === '#' + id);
          });
        });
      }, { rootMargin: '-30% 0px -60% 0px', threshold: 0 });

      targets.forEach(function (target) { observer.observe(target); });
    }

    spyLinks.forEach(function (link) {
      link.addEventListener('click', function () {
        spyLinks.forEach(function (l) { l.classList.remove('is-active'); });
        link.classList.add('is-active');
        if (window.innerWidth <= 980) closeSidebar();
      });
    });
  }

  /* Toast helper for demo interactions */
  var toast = document.getElementById('toast');
  if (!toast) {
    toast = document.createElement('div');
    toast.id = 'toast';
    toast.className = 'toast';
    toast.setAttribute('role', 'status');
    toast.setAttribute('aria-live', 'polite');
    document.body.appendChild(toast);
  }

  var toastTimer;
  window.showToast = function (message) {
    toast.innerHTML = icon('check-circle') + '<span>' + message + '</span>';
    toast.classList.add('is-visible');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () {
      toast.classList.remove('is-visible');
    }, 3200);
  };
})();
