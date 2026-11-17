<?php
// Horario de atención compartido por clientes, administración y personal.
const HORARIO_ATENCION_CLINICA = [
    1 => [['08:30', '12:30'], ['15:00', '19:00']],
    2 => [['08:30', '12:30'], ['15:00', '19:00']],
    3 => [['08:30', '12:30'], ['15:00', '19:00']],
    4 => [['08:30', '12:30'], ['15:00', '19:00']],
    5 => [['08:30', '12:30'], ['15:00', '19:00']],
    6 => [['09:00', '13:00']],
    7 => [],
];

const DURACION_TURNO_MINUTOS = 30;

function horariosClinicaPorDia(): array
{
    $horarios = [];
    foreach (HORARIO_ATENCION_CLINICA as $dia => $franjas) {
        $horarios[$dia] = [];
        foreach ($franjas as [$inicio, $fin]) {
            $actual = DateTimeImmutable::createFromFormat('!H:i', $inicio);
            $limite = DateTimeImmutable::createFromFormat('!H:i', $fin);
            while ($actual && $limite) {
                $siguiente = $actual->modify('+' . DURACION_TURNO_MINUTOS . ' minutes');
                if ($siguiente > $limite) {
                    break;
                }
                $horarios[$dia][] = $actual->format('H:i');
                $actual = $siguiente;
            }
        }
    }
    return $horarios;
}

function fechaClinicaValida(string $fecha): bool
{
    $valor = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha, new DateTimeZone('America/Montevideo'));
    $errores = DateTimeImmutable::getLastErrors();
    return $valor !== false
        && $valor->format('Y-m-d') === $fecha
        && ($errores === false || ($errores['warning_count'] === 0 && $errores['error_count'] === 0));
}

function horaClinicaPermitida(string $fecha, string $hora): bool
{
    if (!fechaClinicaValida($fecha) || !preg_match('/^\d{2}:\d{2}$/', $hora)) {
        return false;
    }

    $dia = (int) DateTimeImmutable::createFromFormat('!Y-m-d', $fecha, new DateTimeZone('America/Montevideo'))->format('N');
    return in_array($hora, horariosClinicaPorDia()[$dia] ?? [], true);
}

function fechaActualMontevideo(): string
{
    return (new DateTimeImmutable('now', new DateTimeZone('America/Montevideo')))->format('Y-m-d');
}

function resumenHorarioClinica(): string
{
    return 'Lunes a viernes: 08:30–12:30 y 15:00–19:00 · Sábados: 09:00–13:00 · Domingos: cerrado';
}
