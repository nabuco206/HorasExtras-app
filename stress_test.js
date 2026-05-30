import http from 'k6/http';
import { sleep, check } from 'k6';

// Configuración de la carga de la prueba
export const options = {
  stages: [
    { duration: '30s', target: 20 }, // Sube a 20 usuarios en 30 segundos
    { duration: '1m', target: 20 },  // Mantiene 20 usuarios por 1 minuto
    { duration: '10s', target: 0 },  // Baja a 0 usuarios
  ],
};

export default function () {
  // Reemplaza con la IP real de tu máquina virtual CentOS
  const url = 'http://127.0.0.1:8000/'; 
  
  const res = http.get(url);

  // Valida que el servidor responda con estado 200
  check(res, {
    'status es 200': (r) => r.status === 200,
  });

  sleep(1); // Espera 1 segundo entre peticiones por usuario
}
