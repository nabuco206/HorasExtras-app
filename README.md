"# HorasExtras-app 27/03/2025" 

CLONAR
git clone <url-del-repositorio>
cd <nombre-del-proyecto>

bajar cambios de un repositorio remoto
git pull

# 1. Hacer cambios en tu código
git add .                    # Agregar archivos al staging
git commit -m "Mensaje"      # Crear commit local
git push origin main         # Enviar al repositorio remoto

Al clonar :
    composer install
    npm install
    [cp .env.example .env] // copia reemplaza
    php artisan key:generate
    php artisan migrate:fresh --seed
    [php artisan storage:link]

    npm run dev
    npm run build 
    php artisan serve

php artisan make:livewire DemoCicloAprobacion
 COMPONENT CREATED  🤙




    🔁 Ahora puedes descargar los últimos cambios desde GitHub con:

        bash
        Copiar
        Editar
        git pull origin master
        Si tu rama no es master (por ejemplo, main), reemplaza master por el nombre correcto:
        
        bash
        Copiar
        Editar
        git pull origin main
        🔍 Ver en qué rama estás:
        Antes de hacer pull, puedes confirmar tu rama actual con:

bash
Copiar
Editar
git branch
    
    
    
    

http://127.0.0.1:8000/admin/login



php artisan serve --host=[IP] --port=[port]

php artisan migrate:fresh --seed

# Verificar enlace simbólico
ls -la public/storage

# Recrear si es necesario
rm public/storage
php artisan storage:link

# Verificar permisos
chmod -R 755 storage/app/public/


php artisan route:list

Ejecuta command
cd /home/administrador/laravel/HorasExtras-app && php artisan bolson:expirar --dry-run

## Comandos útiles

### Base de datos
```bash
# Recrear base de datos con datos de prueba
php artisan migrate:fresh --seed

# Solo ejecutar migraciones
php artisan migrate

# Ver estado de migraciones
php artisan migrate:status
```

### Sistema de bolsón de horas
```bash
# Ver bolsones que expiran (sin ejecutar cambios)
php artisan bolson:expirar --dry-run

# Expirar bolsones vencidos
php artisan bolson:expirar --force

# ========================================
# 🧪 COMANDOS PARA PRUEBAS RÁPIDAS
# ========================================

# Configurar bolsones de prueba (vencen en minutos, no años)
php artisan bolson:test-setup persona01 --reset --duration=5

# Ver resumen detallado del bolsón
php artisan bolson:simular persona01 --resumen

# Simular descuento de minutos (ejemplo: 200 minutos)
php artisan bolson:simular persona01 --descuento=200

# Crear datos de ejemplo para simulación
php artisan bolson:simular persona01 --setup
```

### Desarrollo
```bash
# Listar rutas disponibles
php artisan route:list

# Iniciar servidor en IP y puerto específico
php artisan serve --host=[IP] --port=[port]
```

## 🧪 Plan de Pruebas del Sistema de Bolsón

### Preparación inicial
```bash
# 1. Recrear base de datos limpia
php artisan migrate:fresh --seed

# 2. Configurar bolsones de prueba (vencen en minutos)
php artisan bolson:test-setup persona01 --reset --duration=5
```

### Escenarios de prueba

#### 📋 **Escenario 1: Verificar FIFO (First In, First Out)**
```bash
# Ver estado inicial
php artisan bolson:simular persona01 --resumen

# Simular descuento de 200 minutos (debería usar primero el que vence antes)
php artisan bolson:simular persona01 --descuento=200

# Verificar resultado
php artisan bolson:simular persona01 --resumen
```

#### ⏰ **Escenario 2: Probar expiraciones automáticas**
```bash
# Esperar 3-4 minutos y luego ejecutar:
php artisan bolson:expirar --dry-run
php artisan bolson:expirar --force

# Verificar bolsones expirados
php artisan bolson:simular persona01 --resumen
```

#### 🌐 **Escenario 3: Probar interfaz web**
```bash
# Iniciar servidor
php artisan serve --port=8001

# Acceder a:
# - Dashboard: http://localhost:8001/dashboard
# - Ingreso HE: http://localhost:8001/sistema/ingreso-he
# - Compensaciones: http://localhost:8001/sistema/ingreso-compensacion
```

#### 🔄 **Escenario 4: Flujo completo**
1. **Crear HE** → Genera bolsón con tiempo disponible
2. **Solicitar compensación** → Descuenta tiempo usando FIFO  
3. **Monitorear dashboard** → Ver saldos actualizados
4. **Esperar vencimiento** → Ver expiración automática

### Comandos de monitoreo
```bash
# Monitoreo en tiempo real (ejecutar cada minuto)
watch -n 60 "php artisan bolson:simular persona01 --resumen"

# Ver log de la aplicación
tail -f storage/logs/laravel.log

# Reset completo para nuevas pruebas
php artisan bolson:test-setup persona01 --reset --duration=3