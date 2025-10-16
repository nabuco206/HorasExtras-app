    public function obtenerDetalleSaldo(string $username): array
    {
        $bolsones = TblBolsonTiempo::vigentes()
            ->where('username', $username)
            ->orderBy('fecha_crea', 'asc')
            ->get();

        return $bolsones->map(function ($bolson) {
            $diasRestantes = now()->diffInDays($bolson->fecha_vence, false);
            
            return [
                'id' => $bolson->id,
                'solicitud_he_id' => $bolson->id_solicitud_he,
                'minutos_iniciales' => $bolson->minutos,
                'minutos_disponibles' => $bolson->saldo_min,
                'saldo_min' => $bolson->saldo_min,
                'fecha_vence' => $bolson->fecha_vence->toDateString(),
                'fecha_vencimiento' => $bolson->fecha_vence->toDateString(),
                'dias_restantes' => $diasRestantes,
                'descripcion' => 'Bolsón de HE'
            ];
        })->toArray();
    }
