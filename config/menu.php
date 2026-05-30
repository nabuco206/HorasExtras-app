<?php

$NameSeccion_FUN = 'Funcionario';
$NameSeccion_JD = 'Jefe Directo';
$NameSeccion_UDP = 'Unidad de Personas';
$NameSeccion_JUDP = 'Jefe Unidad de Personasssss';
$NameSeccion_DER = 'Dirección Ejecutiva';

// === BLOQUES REUTILIZABLES ===
$blocks = [

    // Base: Funcionario
    'funcionario_base' => [
        [
            'section' => $NameSeccion_FUN,
            'name' => 'Dashboard',
            'icon' => 'home',
            'route' => 'dashboard',
        ],
        [
            'section' => $NameSeccion_FUN,
            'name' => 'Ingreso Horas Extraordinarias',
            'icon' => 'inbox-arrow-down',
            'route' => 'sistema.ingreso-he',
        ],
        [
            'section' => $NameSeccion_FUN,
            'name' => 'Solicitud Compensación',
            'icon' => 'cube-transparent',
            'route' => 'sistema.ingreso-compensacion',
        ],
    ],

    // Base: Jefe Directo (JD)
    'jd_base' => [
        [
            'section' => $NameSeccion_JD,
            'name' => 'Dashboard',
            'icon' => 'home',
            'route' => 'dashboard',
        ],
        [
            'section' => $NameSeccion_JD,
            'name' => 'Aprobar HE JD',
            'codigo' => 'JD_aprobar_he',
            'icon' => 'check-circle',
            'route' => 'sistema.aprobaciones-unificadas',
            'params' => ['tipo' => 1, 'rol' => 2, 'estado' => 1],
            'titulo' => '🕙 Aprobación de Horas Extra JD',
        ],
        [
            'section' => $NameSeccion_JD,
            'name' => 'Aprobar Pago JD',
            'codigo' => 'JD_aprobar_pago',
            'icon' => 'banknotes',
            'route' => 'sistema.aprobaciones-unificadas',
            'params' => ['tipo' => 2, 'rol' => 2, 'estado' => 1],
            'titulo' => '💰 Aprobación Pago de Horas Extra JD',
        ],
        [
            'section' => $NameSeccion_JD,
            'name' => 'Aprobar Comp. JD',
            'codigo' => 'JD_aprobar_compensacion',
            'icon' => 'banknotes',
            'route' => 'sistema.aprobaciones-compensacion',
        ],
        [
            'section' => $NameSeccion_JD,
            'name' => 'Mi Equipo',
            'icon' => 'users',
            'route' => 'sistema.mi-equipo',
        ],
        [
            'section' => $NameSeccion_JD,
            'name' => 'Calendario JD',
            'icon' => 'calendar',
            'route' => 'sistema.calendario-jd',
        ],
        [
            'section' => $NameSeccion_JD,
            'name' => 'Solicitudes a pago',
            'icon' => 'banknotes',
            'route' => 'sistema.solicitudes-pago',
        ],
        [
            'section' => $NameSeccion_JD,
            'name' => 'Todas las Compensaciones',
            'icon' => 'banknotes',
            'route' => 'sistema.todas-compensaciones',
        ],
    ],

    // Extras: Unidad de Personas (UDP)
    'udp_extras' => [
        [
            'section' => $NameSeccion_UDP,
            'name' => 'Panel de Admin',
            'icon' => 'shield-check',
            'url' => '/admin',
            'target' => '_blank',
            'rel' => 'noopener noreferrer',
        ],
        [
            'section' => $NameSeccion_UDP,
            'codigo' => 'JD_aprobar_pago',
            'icon' => 'banknotes',
            'name' => 'Aprobar Pago UDP',
            'icon' => 'banknotes',
            'route' => 'sistema.aprobaciones-unificadas',
            'params' => ['tipo' => 2, 'rol' => 3, 'estado' => 2],
            'titulo' => '💰 Aprobación Pago de Horas Extra UDP',
        ],
        [
            'section' => $NameSeccion_UDP,
            'name' => 'Monitoreo de Tiempo',
            'icon' => 'chart-bar',
            'route' => 'sistema.monitoreo-tiempo',
        ],
        [
            'section' => $NameSeccion_UDP,
            'name' => 'Dashboard de Tiempo',
            'icon' => 'presentation-chart-bar',
            'route' => 'sistema.dashboard-tiempo',
        ],
        // Nota: "Todas las Compensaciones" ya está en jd_base → no duplicar
    ],

    // Extras: Jefe UDP (JUDP)
    'judp_extras' => [
        [
            'section' => $NameSeccion_JUDP,
            'name' => 'Aprobar Pago JUDP',
            'codigo' => 'JD_aprobar_pago',
            'icon' => 'clipboard-document-check',
            'route' => 'sistema.aprobaciones-unificadas',
            'params' => ['tipo' => 2, 'rol' => 4, 'estado' => 3],
            'titulo' => 'Aprobación Pago de Horas Extra JUDP',
        ],
         [
            'name' => 'Gasto en HE',
            'route' => 'powerbi.dashboard',
            'icon' => 'chart-pie',
            'section' => 'Informes',
            'codigo' => null,
        ],
    ],

    // Extras: Dirección Ejecutiva (DER)
    'der_extras' => [
        [
            'section' => $NameSeccion_DER,
            'name' => 'Aprobar Pago DER',
            'icon' => 'banknotes',
            'route' => 'sistema.aprobaciones-unificadas',
            'params' => ['tipo' => 2, 'rol' => 5, 'estado' => 4],
            'titulo' => 'Aprobar Pago DER',
        ],
        [
            'section' => $NameSeccion_DER,
            'name' => 'Dashboard de Tiempo',
            'icon' => 'presentation-chart-bar',
            'route' => 'sistema.dashboard-tiempo',
        ],
        [
            'section' => $NameSeccion_DER,
            'name' => 'Todas las Compensaciones',
            'icon' => 'banknotes',
            'route' => 'sistema.todas-compensaciones',
        ],
          [
            'name' => 'Gasto en HE',
            'route' => 'powerbi.dashboard',
            'icon' => 'chart-pie',
            'section' => 'Informes',
            'codigo' => null,
        ],
         [
            'name' => 'Gasto en HE (nueva pestaña)',
            'route' => 'powerbi.dashboard',
            'icon' => 'arrow-top-right-on-square',
            'section' => 'Informes',
            'codigo' => null,
            'target' => '_blank', // marca para que el renderer añada target="_blank"
        ],
    ],
];
// === DEFINICIÓN DE ROLES POR COMPOSICIÓN ===
return [

    'blocks' => $blocks,

    'roles' => [
        // 1: Funcionario
        '1' => $blocks['funcionario_base'],

        // 2: Jefe Directo
        '2' => $blocks['jd_base'],

        // 3: UDP → es funcionario + extras UDP
        '3' => array_merge(
            $blocks['funcionario_base'],
            $blocks['udp_extras']
        ),

        // 4: JUDP → es JD + extras JUDP
        '4' => array_merge(
            $blocks['jd_base'],
            $blocks['judp_extras']
        ),

        // 5: DER → actualmente es JD + extras DER
        '5' => array_merge(
            $blocks['jd_base'],
            $blocks['der_extras']
        ),

        // 6: Líder de fiscalía → es funcionario + extras de JD
        '6' => array_merge(
            $blocks['funcionario_base'],
            $blocks['jd_base']
        ),

        // ✨ Futuro: si DER deja de ser JD, solo cambia a:
        // '5' => array_merge(
        //     $blocks['funcionario_base'], // o un nuevo 'der_base'
        //     $blocks['der_extras']
        // ),

        // 6 => [
        //     [
        //         'name' => 'Power BI',
        //         'route' => 'powerbi.dashboard',
        //         'icon' => 'chart-pie',
        //         'section' => 'Informes',
        //         'codigo' => null,
        //     ],
        // ],
    ],
];
