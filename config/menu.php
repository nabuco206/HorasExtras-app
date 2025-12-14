<?php

return [
    'roles' => [
        // USUARIO NORMAL
        '1' => [
            [
                'name' => 'Dashboard',
                'icon' => 'home',
                'route' => 'dashboard',
            ],
            [
                'name' => 'Ingreso Horas Extraordinarias',
                'icon' => 'inbox-arrow-down',
                'route' => 'sistema.ingreso-he',
            ],
            [
                'name' => 'Solicitud Compensación',
                'icon' => 'cube-transparent',
                'route' => 'sistema.ingreso-compensacion',
            ],
        ],
        // Jefe Directo
        '2' => [
              [
                'name' => 'Dashboard',
                'icon' => 'home',
                'route' => 'dashboard',
            ],
            [
                'name' => 'Aprobar HE JD',
                'icon' => 'check-circle',
                'route' => 'sistema.aprobaciones-unificadas',
                'params' => ['tipo' => 1, 'rol' => 2, 'estado' => 1],
                'titulo' => 'Aprobación Compensacion en Tiempo JD',
            ],
            [
                'name' => 'Aprobar Pago JD',
                'icon' => 'banknotes',
                'route' => 'sistema.aprobaciones-unificadas',
                'params' => ['tipo' => 2, 'rol' => 2, 'estado' => 1],
                'titulo' => 'Aprobación Compensacion en Pago JD',
            ],
            [
                'name' => 'Aprobar Compensaciones JD',
                'icon' => 'banknotes',
                'route' => 'sistema.aprobaciones-compensacion',
            ],
            [
                'name' => 'Mi Equipo',
                'icon' => 'users',
                'route' => 'sistema.mi-equipo',
            ],
            [
                'name' => 'Calendario JD',
                'icon' => 'calendar',
                'route' => 'sistema.calendario-jd',
            ],
            [
                'name' => 'Solicitudes a pago',
                'icon' => 'banknotes',
                'route' => 'sistema.solicitudes-pago',
            ],
             [
                'name' => 'Todas las Compensaciones',
                'icon' => 'banknotes',
                'route' => 'sistema.todas-compensaciones', // Esta ruta coincide con la definida en web.php
            ],
           
            
        ],
        // UDP
        '3' => [
             [
                'name' => 'Dashboard',
                'icon' => 'home',
                'route' => 'dashboard',
            ],
             [
            'name' => 'Panel de Admin',
            'icon' => 'shield-check',
            'route' => null,
            'url' => '/admin',
            'current' => false,
            'target' => '_blank'
            ],
            // [
            //     'name' => 'Aprobar HE UDP',
            //     'icon' => 'check-circle',
            //     'route' => 'sistema.aprobaciones-unificadas',
            //     // UDP debe enviar rol=3 para indicar vista administrativa global
            //     'params' => ['tipo' => 1, 'rol' => 3, 'estado' => 1],
            //     'titulo' => 'Aprobación de Horas Extra UDP', // Este campo será pasado como parámetro en la ruta
            // ],
            [
                'name' => 'Aprobar Pago UDP',
                'icon' => 'banknotes',
                'route' => 'sistema.aprobaciones-unificadas',
                'params' => ['tipo' => 2, 'rol' => 3, 'estado' => 2],
                'titulo' => 'Aprobación Pago de Horas Extra UDP',
            ],
            [
                'name' => 'Solicitudes a pago',
                'icon' => 'banknotes',
                'route' => 'sistema.solicitudes-pago',
            ],
            [
                'name' => 'Monitoreo de Tiempo',
                'icon' => 'chart-bar',
                'route' => 'sistema.monitoreo-tiempo',
            ],
            [
                'name' => 'Dashboard de Tiempo',
                'icon' => 'presentation-chart-bar',
                'route' => 'sistema.dashboard-tiempo',
            ],
            [
                'name' => 'Todas las Compensaciones',
                'icon' => 'banknotes',
                'route' => 'sistema.todas-compensaciones',
            ],
        ],
        // JUDP
        '4' => [
             [
                'name' => 'Dashboard',
                'icon' => 'home',
                'route' => 'dashboard',
            ],
            [
                'name' => 'Aprobaciones JUDP',
                'icon' => 'clipboard-document-check',
                'route' => 'sistema.aprobaciones-unificadas',
                'titulo' => 'Aprobación Pago de Horas Extra JUDP',
                'params' => ['tipo' => 2, 'rol' => 4, 'estado' => 3],
            ],
            [
                'name' => 'Solicitudes a pago',
                'icon' => 'banknotes',
                'route' => 'sistema.solicitudes-pago',
            ],
            [
                'name' => 'Monitoreo de Tiempo',
                'icon' => 'chart-bar',
                'route' => 'sistema.monitoreo-tiempo',
            ],
            [
                'name' => 'Dashboard de Tiempo',
                'icon' => 'presentation-chart-bar',
                'route' => 'sistema.dashboard-tiempo',
            ],
        ],
        // DER
        '5' => [
         
            [
                'name' => 'Dashboard',
                'icon' => 'home',
                'route' => 'dashboard',
            ],
            [
                'name' => 'Aprobar Pago DER',
                'icon' => 'banknotes',
                'route' => 'sistema.aprobaciones-unificadas',
                'params' => ['tipo' => 2, 'rol' => 5, 'estado' => 4],
                'titulo' => 'Aprobar Pago DER',
            ],
            [
                'name' => 'Solicitudes a pago',
                'icon' => 'banknotes',
                'route' => 'sistema.solicitudes-pago',
            ],
            [
                'name' => 'Monitoreo de Tiempo',
                'icon' => 'chart-bar',
                'route' => 'sistema.monitoreo-tiempo',
            ],
            [
                'name' => 'Dashboard de Tiempo',
                'icon' => 'presentation-chart-bar',
                'route' => 'sistema.dashboard-tiempo',
            ],
            [
                'name' => 'Todas las Compensaciones',
                'icon' => 'banknotes',
                'route' => 'sistema.todas-compensaciones',
            ],
        ],
    ],
   
];
