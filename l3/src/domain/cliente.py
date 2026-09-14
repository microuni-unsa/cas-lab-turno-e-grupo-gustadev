"""
Módulo de dominio para la gestión de clientes y cuentas de pago.
Caso de Estudio 3 - Laboratorio 3 (Calidad de Software).
"""

from typing import List, Optional


class SaldoInsuficienteError(Exception):
    """Excepción lanzada cuando una cuenta no dispone de fondos suficientes."""
    pass


class CuentaSinFondosError(Exception):
    """Excepción lanzada cuando un cliente intenta ordenar sin cuentas con saldo."""
    pass


class TarjetaCredito:
    """Representa una tarjeta de crédito asociada a una cuenta."""

    def __init__(self, numero: str, titular: str, fecha_vencimiento: str):
        self.numero = numero
        self.titular = titular
        self.fecha_vencimiento = fecha_vencimiento

    def __repr__(self) -> str:
        return f"TarjetaCredito(numero=...{self.numero[-4:]})"


class CuentaPago:
    """Representa una cuenta de pago asociada a una tarjeta de crédito."""

    def __init__(self, id_cuenta: str, tarjeta: TarjetaCredito, saldo_inicial: float = 0.0):
        self.id_cuenta = id_cuenta
        self.tarjeta = tarjeta
        self.saldo_disponible = float(saldo_inicial)

    def aumentar_saldo(self, monto: float) -> None:
        """Permite al cliente recargar fondos periódicamente."""
        if monto <= 0:
            raise ValueError("El monto a recargar debe ser positivo.")
        self.saldo_disponible += monto

    def debitar(self, monto: float) -> None:
        """Debita el importe del saldo de la cuenta."""
        if monto > self.saldo_disponible:
            raise SaldoInsuficienteError(
                f"Saldo insuficiente en cuenta {self.id_cuenta}. Disponible: {self.saldo_disponible:.2f}, Requerido: {monto:.2f}"
            )
        self.saldo_disponible -= monto

    def tiene_saldo(self) -> bool:
        """Verifica si la cuenta cuenta con saldo positivo disponible."""
        return self.saldo_disponible > 0.0

    def __repr__(self) -> str:
        return f"CuentaPago(id={self.id_cuenta}, saldo={self.saldo_disponible:.2f})"


class Cliente:
    """Entidad Cliente que administra sus datos de contacto y cuentas de pago."""

    def __init__(self, id_cliente: str, nombre: str, direccion: str, telefono: str, email: str):
        self.id_cliente = id_cliente
        self.nombre = nombre
        self.direccion = direccion
        self.telefono = telefono
        self.email = email
        self.cuentas: List[CuentaPago] = []

    def agregar_cuenta(self, cuenta: CuentaPago) -> None:
        """Asocia una nueva cuenta de pago al cliente."""
        self.cuentas.append(cuenta)

    def tiene_cuenta_con_dinero(self) -> bool:
        """
        Regla de negocio: Un cliente puede empezar a realizar un pedido sólo
        si tiene alguna cuenta con dinero disponible.
        """
        return any(c.tiene_saldo() for c in self.cuentas)

    def obtener_cuenta(self, id_cuenta: str) -> Optional[CuentaPago]:
        """Obtiene una cuenta por su identificador si pertenece al cliente."""
        for c in self.cuentas:
            if c.id_cuenta == id_cuenta:
                return c
        return None

    def posee_cuenta(self, cuenta: CuentaPago) -> bool:
        """Verifica si la cuenta pertenece a la colección del cliente."""
        return cuenta in self.cuentas

    def __repr__(self) -> str:
        return f"Cliente(id={self.id_cliente}, nombre={self.nombre})"
