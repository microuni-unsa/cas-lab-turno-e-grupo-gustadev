"""
Módulo de jerarquía de pedidos empleando el Patrón Composite.
Caso de Estudio 3 - Laboratorio 3 (Calidad de Software).
"""

from abc import ABC, abstractmethod
from enum import Enum
from typing import List, Set
from .cliente import Cliente, CuentaPago, SaldoInsuficienteError
from .producto import LineaPedido, Producto


class EstadoPedido(Enum):
    PENDIENTE = "Pendiente de cobro"
    COBRADO = "Cobrado / Listo para servir"
    EN_DISTRIBUCION = "En distribución"
    CONFIRMADO = "Confirmado / Entregado"
    RECHAZADO = "Rechazado"


class ReglaNegocioError(Exception):
    """Excepción lanzada cuando se violan las restricciones del dominio."""
    pass


class Pedido(ABC):
    """
    Componente abstracto del Patrón Composite.
    Define la interfaz común para pedidos simples y pedidos compuestos.
    """

    def __init__(self, id_pedido: str, cliente: Cliente):
        self.id_pedido = id_pedido
        self.cliente = cliente
        self.estado = EstadoPedido.PENDIENTE

    @abstractmethod
    def calcular_total(self) -> float:
        """Calcula el costo total del pedido."""
        pass

    @abstractmethod
    def total_unidades(self) -> int:
        """Retorna la cantidad total de unidades contenidas."""
        pass

    @abstractmethod
    def obtener_cuentas_asociadas(self) -> Set[CuentaPago]:
        """Obtiene todas las cuentas involucradas en el pago de los ítems."""
        pass

    @abstractmethod
    def validar_invariantes(self) -> None:
        """Verifica que se cumplan las restricciones de integridad."""
        pass

    def marcar_estado(self, nuevo_estado: EstadoPedido) -> None:
        """Actualiza el estado del pedido recursivamente."""
        self.estado = nuevo_estado

    def __repr__(self) -> str:
        return f"{self.__class__.__name__}(id={self.id_pedido}, estado={self.estado.value}, total={self.calcular_total():.2f})"


class PedidoSimple(Pedido):
    """
    Elemento Hoja (Leaf) del Patrón Composite.
    Restricciones:
    - Asociado a una única cuenta de pago.
    - Contiene un máximo de 20 unidades del mismo o distinto tipo de producto.
    - La cuenta de pago debe pertenecer al cliente que realiza el pedido.
    """

    LIMITE_MAX_UNIDADES = 20

    def __init__(self, id_pedido: str, cliente: Cliente, cuenta_pago: CuentaPago):
        super().__init__(id_pedido, cliente)
        if not cliente.posee_cuenta(cuenta_pago):
            raise ReglaNegocioError(
                f"La cuenta {cuenta_pago.id_cuenta} no pertenece al cliente {cliente.id_cliente}."
            )
        self.cuenta_pago = cuenta_pago
        self.lineas: List[LineaPedido] = []

    def agregar_linea(self, producto: Producto, cantidad: int) -> None:
        """Añade una línea de producto controlando el tope de 20 unidades."""
        if self.total_unidades() + cantidad > self.LIMITE_MAX_UNIDADES:
            raise ReglaNegocioError(
                f"Restricción de distribución excedida: el pedido simple no puede superar {self.LIMITE_MAX_UNIDADES} "
                f"unidades totales. (Actuales: {self.total_unidades()}, Intentando sumar: {cantidad})"
            )
        linea = LineaPedido(producto, cantidad)
        self.lineas.append(linea)

    def calcular_total(self) -> float:
        return sum(linea.subtotal for linea in self.lineas)

    def total_unidades(self) -> int:
        return sum(linea.cantidad for linea in self.lineas)

    def obtener_cuentas_asociadas(self) -> Set[CuentaPago]:
        return {self.cuenta_pago}

    def validar_invariantes(self) -> None:
        if self.total_unidades() > self.LIMITE_MAX_UNIDADES:
            raise ReglaNegocioError(f"Pedido simple {self.id_pedido} excede el límite de {self.LIMITE_MAX_UNIDADES} unidades.")
        if not self.cliente.posee_cuenta(self.cuenta_pago):
            raise ReglaNegocioError(f"Cuenta {self.cuenta_pago.id_cuenta} no pertenece al cliente {self.cliente.id_cliente}.")


class PedidoCompuesto(Pedido):
    """
    Elemento Compuesto (Composite) del Patrón Composite.
    Restricciones:
    - Contiene dos o más pedidos (que pueden ser simples o compuestos).
    - Todos los pedidos simples contenidos deben pagarse con cuentas del mismo cliente.
    """

    def __init__(self, id_pedido: str, cliente: Cliente):
        super().__init__(id_pedido, cliente)
        self.pedidos_hijos: List[Pedido] = []

    def agregar_subpedido(self, subpedido: Pedido) -> None:
        """Añade un pedido hijo garantizando la correspondencia de cliente."""
        if subpedido.cliente.id_cliente != self.cliente.id_cliente:
            raise ReglaNegocioError(
                f"Incoherencia de cliente: el subpedido pertenece a {subpedido.cliente.id_cliente}, "
                f"pero el pedido compuesto pertenece a {self.cliente.id_cliente}."
            )
        self.pedidos_hijos.append(subpedido)

    def calcular_total(self) -> float:
        return sum(p.calcular_total() for p in self.pedidos_hijos)

    def total_unidades(self) -> int:
        return sum(p.total_unidades() for p in self.pedidos_hijos)

    def obtener_cuentas_asociadas(self) -> Set[CuentaPago]:
        cuentas: Set[CuentaPago] = set()
        for p in self.pedidos_hijos:
            cuentas.update(p.obtener_cuentas_asociadas())
        return cuentas

    def marcar_estado(self, nuevo_estado: EstadoPedido) -> None:
        """Propaga recursivamente el estado a todos los subpedidos."""
        super().marcar_estado(nuevo_estado)
        for p in self.pedidos_hijos:
            p.marcar_estado(nuevo_estado)

    def validar_invariantes(self) -> None:
        if len(self.pedidos_hijos) < 2:
            raise ReglaNegocioError(
                f"Un pedido compuesto debe contener al menos 2 pedidos hijos (actualmente contiene {len(self.pedidos_hijos)})."
            )
        # Verificar que todas las cuentas asociadas pertenezcan al cliente del pedido compuesto
        for cuenta in self.obtener_cuentas_asociadas():
            if not self.cliente.posee_cuenta(cuenta):
                raise ReglaNegocioError(
                    f"Violación de invariante: la cuenta {cuenta.id_cuenta} no pertenece al cliente {self.cliente.id_cliente}."
                )
        for p in self.pedidos_hijos:
            p.validar_invariantes()
