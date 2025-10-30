<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Demo Sistema de Workflow</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Demo Sistema de Workflow</h1>
            <p class="text-gray-600">Prueba el nuevo sistema de flujos de aprobación configurables</p>
        </div>

        <!-- Secciones principales -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8" x-data="workflowDemo()">

            <!-- Flujos Disponibles -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4 text-gray-800">Flujos Disponibles</h2>
                <div class="space-y-3">
                    @foreach($flujos as $flujo)
                    <div class="border rounded-lg p-4 hover:bg-gray-50 cursor-pointer"
                         @click="seleccionarFlujo({{ $flujo->id }})">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-medium text-gray-800">{{ $flujo->nombre }}</h3>
                                <p class="text-sm text-gray-600 mt-1">{{ $flujo->descripcion }}</p>
                            </div>
                            <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">
                                Activo
                            </span>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Detalle del flujo seleccionado -->
                <div x-show="flujoSeleccionado" class="mt-6 border-t pt-6">
                    <h3 class="font-semibold mb-3">Detalle del Flujo</h3>
                    <div x-show="cargandoFlujo" class="text-center py-4">
                        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
                    </div>
                    <div x-show="detalleFlujo && !cargandoFlujo">
                        <div class="mb-4">
                            <h4 class="font-medium text-gray-700 mb-2">Estados:</h4>
                            <div class="flex flex-wrap gap-2">
                                <template x-for="estado in detalleFlujo.estados" :key="estado.id">
                                    <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full"
                                          x-text="estado.gls_estado"></span>
                                </template>
                            </div>
                        </div>
                        <div>
                            <h4 class="font-medium text-gray-700 mb-2">Transiciones:</h4>
                            <div class="space-y-2 max-h-40 overflow-y-auto">
                                <template x-for="transicion in detalleFlujo.transiciones" :key="transicion.orden">
                                    <div class="text-sm bg-gray-50 p-2 rounded">
                                        <span x-text="transicion.origen.gls_estado"></span>
                                        →
                                        <span x-text="transicion.destino.gls_estado"></span>
                                        <span x-show="transicion.rol_autorizado"
                                              class="ml-2 text-xs bg-yellow-100 text-yellow-800 px-1 rounded"
                                              x-text="transicion.rol_autorizado"></span>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Solicitudes de Prueba -->
            <div class="bg-white rounded-lg shadow-md p-6">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-xl font-semibold text-gray-800">Solicitudes de Prueba</h2>
                    <button @click="crearSolicitudPrueba()"
                            class="bg-green-600 text-white text-sm px-4 py-2 rounded hover:bg-green-700">
                        + Crear Solicitud
                    </button>
                </div>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($solicitudes as $solicitud)
                    <div class="border rounded-lg p-4 hover:bg-gray-50 cursor-pointer"
                         @click="seleccionarSolicitud({{ $solicitud->id }})">
                        <div class="flex justify-between items-start">
                            <div>
                                <h3 class="font-medium text-gray-800">
                                    Solicitud #{{ $solicitud->id }}
                                </h3>
                                <p class="text-sm text-gray-600">
                                    Usuario: {{ $solicitud->username ?? 'N/A' }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    Tipo: {{ $solicitud->tipoCompensacion->descripcion ?? 'N/A' }}
                                </p>
                                <p class="text-sm text-gray-600">
                                    Total: {{ $solicitud->total_min }} minutos
                                </p>
                            </div>
                            <div class="text-right">
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-1 rounded-full">
                                    {{ $solicitud->estado->gls_estado ?? 'Sin Estado' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <!-- Panel de control de la solicitud seleccionada -->
                <div x-show="solicitudSeleccionada" class="mt-6 border-t pt-6">
                    <h3 class="font-semibold mb-3">Control de Solicitud</h3>

                    <!-- Transiciones disponibles -->
                    <div x-show="transicionesDisponibles.length > 0" class="mb-4">
                        <h4 class="font-medium text-gray-700 mb-2">Transiciones Disponibles:</h4>
                        <div class="space-y-2">
                            <template x-for="transicion in transicionesDisponibles" :key="transicion.estado_destino_id">
                                <div class="flex items-center justify-between bg-gray-50 p-2 rounded">
                                    <span x-text="transicion.estado_destino" class="text-sm"></span>
                                    <button @click="ejecutarTransicion(transicion.estado_destino_id)"
                                            class="bg-blue-600 text-white text-xs px-3 py-1 rounded hover:bg-blue-700">
                                        Ejecutar
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Campo de observaciones -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Observaciones (opcional):
                        </label>
                        <textarea x-model="observaciones"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 text-sm"
                                  rows="3" placeholder="Ingrese observaciones..."></textarea>
                    </div>

                    <!-- Historial -->
                    <div>
                        <h4 class="font-medium text-gray-700 mb-2">Historial:</h4>
                        <button @click="cargarHistorial()"
                                class="bg-gray-600 text-white text-sm px-3 py-1 rounded hover:bg-gray-700 mb-2">
                            Actualizar Historial
                        </button>
                        <div x-show="historial.length > 0" class="space-y-2 max-h-40 overflow-y-auto">
                            <template x-for="entrada in historial" :key="entrada.id">
                                <div class="text-xs bg-gray-50 p-2 rounded">
                                    <div class="font-medium" x-text="entrada.descripcion"></div>
                                    <div class="text-gray-600">
                                        Por: <span x-text="entrada.usuario"></span> -
                                        <span x-text="entrada.fecha_cambio"></span>
                                    </div>
                                    <div x-show="entrada.observaciones" class="mt-1 italic"
                                         x-text="entrada.observaciones"></div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensajes -->
        <div x-show="mensaje"
             :class="{'bg-green-100 border-green-400 text-green-700': mensajeTipo === 'success',
                      'bg-red-100 border-red-400 text-red-700': mensajeTipo === 'error'}"
             class="mt-6 border px-4 py-3 rounded relative">
            <span x-text="mensaje"></span>
            <button @click="mensaje = ''" class="absolute top-0 bottom-0 right-0 px-4 py-3">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    </div>

    <script>
        function workflowDemo() {
            return {
                flujoSeleccionado: null,
                detalleFlujo: null,
                cargandoFlujo: false,
                solicitudSeleccionada: null,
                transicionesDisponibles: [],
                historial: [],
                observaciones: '',
                mensaje: '',
                mensajeTipo: 'success',

                async seleccionarFlujo(flujoId) {
                    this.flujoSeleccionado = flujoId;
                    this.cargandoFlujo = true;
                    this.detalleFlujo = null;

                    try {
                        const response = await fetch(`/workflow/flujo/${flujoId}`, {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        const data = await response.json();

                        if (data.success) {
                            this.detalleFlujo = data.data;
                        } else {
                            this.mostrarMensaje('Error al cargar flujo: ' + data.message, 'error');
                        }
                    } catch (error) {
                        this.mostrarMensaje('Error de conexión: ' + error.message, 'error');
                    } finally {
                        this.cargandoFlujo = false;
                    }
                },

                async seleccionarSolicitud(solicitudId) {
                    this.solicitudSeleccionada = solicitudId;
                    await this.cargarTransiciones();
                    await this.cargarHistorial();
                },

                async cargarTransiciones() {
                    if (!this.solicitudSeleccionada) return;

                    try {
                        const response = await fetch(`/workflow/solicitud/${this.solicitudSeleccionada}/transiciones`, {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        const data = await response.json();

                        if (data.success) {
                            this.transicionesDisponibles = data.data.transiciones_disponibles;
                        } else {
                            this.mostrarMensaje('Error al cargar transiciones: ' + data.message, 'error');
                        }
                    } catch (error) {
                        this.mostrarMensaje('Error de conexión: ' + error.message, 'error');
                    }
                },

                async ejecutarTransicion(estadoDestinoId) {
                    if (!this.solicitudSeleccionada) return;

                    try {
                        const response = await fetch(`/workflow/solicitud/${this.solicitudSeleccionada}/transicion`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                estado_destino_id: estadoDestinoId,
                                observaciones: this.observaciones
                            })
                        });
                        const data = await response.json();

                        if (data.success) {
                            this.mostrarMensaje('Transición ejecutada correctamente', 'success');
                            this.observaciones = '';
                            await this.cargarTransiciones();
                            await this.cargarHistorial();
                        } else {
                            this.mostrarMensaje('Error: ' + data.message, 'error');
                        }
                    } catch (error) {
                        this.mostrarMensaje('Error de conexión: ' + error.message, 'error');
                    }
                },

                async cargarHistorial() {
                    if (!this.solicitudSeleccionada) return;

                    try {
                        const response = await fetch(`/workflow/solicitud/${this.solicitudSeleccionada}/historial`, {
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        const data = await response.json();

                        if (data.success) {
                            this.historial = data.data.historial;
                        } else {
                            this.mostrarMensaje('Error al cargar historial: ' + data.message, 'error');
                        }
                    } catch (error) {
                        this.mostrarMensaje('Error de conexión: ' + error.message, 'error');
                    }
                },

                mostrarMensaje(texto, tipo = 'success') {
                    this.mensaje = texto;
                    this.mensajeTipo = tipo;
                    setTimeout(() => {
                        this.mensaje = '';
                    }, 5000);
                },

                async crearSolicitudPrueba() {
                    try {
                        const response = await fetch('/workflow/crear-solicitud-prueba', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                username: 'usuario_' + Date.now(),
                                total_minutos: Math.floor(Math.random() * 300) + 60 // Entre 60 y 360 minutos
                            })
                        });
                        const data = await response.json();

                        if (data.success) {
                            this.mostrarMensaje(`Solicitud #${data.data.solicitud_id} creada para ${data.data.username} (${data.data.total_minutos} minutos)`, 'success');
                            // Recargar la página para mostrar la nueva solicitud
                            setTimeout(() => {
                                window.location.reload();
                            }, 2000);
                        } else {
                            this.mostrarMensaje('Error: ' + data.message, 'error');
                        }
                    } catch (error) {
                        this.mostrarMensaje('Error de conexión: ' + error.message, 'error');
                    }
                }
            }
        }
    </script>
</body>
</html>
