"""
Módulo del gestor centralizado de cobros y distribución implementando el Patrón Singleton.
Caso de Estudio 3 - Laboratorio 3 (Calidad de Software).
"""

from typing import Dict, List, Optional
from .pedido import EstadoPedido, Pedido, PedidoCompuesto, PedidoSimple, ReglaNegocioError
from .cliente import SaldoInsuficienteError, CuentaPago


class GestorProcesamientoPedidos:
    """
    Patrón Singleton: Clase única responsable de:
    - Ejecutar el cobro diario por lotes.
    - Ordenar la distribución de pedidos listos.
    - Confirmar la entrega final de los pedidos.
    """

    _instancia: Optional["GestorProcesamientoPedidos"] = None

    def __new__(cls) -> "GestorProcesamientoPedidos":
        if cls._instancia is None:
            cls._instancia = super(GestorProcesamientoPedidos, cls).__new__(cls)
            cls._instancia._inicializar()
        return cls._instancia

    def _inicializar(self) -> None:
        self.pedidos_registrados: List[Pedido] = []
        self.historial_rechazos: List[Dict[str, str]] = []

    @classmethod
    def reiniciar_instancia(cls) -> None:
        """Método utilitario para reiniciar estado en pruebas automatizadas."""
        cls._instancia = None

    def registrar_pedido(self, pedido: Pedido) -> None:
        """Registra un nuevo pedido en la cola de procesamiento del sistema."""
        pedido.validar_invariantes()
        self.pedidos_registrados.append(pedido)

    def ejecutar_cobro_diario(self) -> Dict[str, int]:
        """
        Ejecuta el proceso diario de cobro sobre todos los pedidos pendientes:
        - Si una cuenta no tiene suficiente dinero, el pedido se rechaza.
        - Si el pedido rechazado es parte de un pedido compuesto, se rechaza el pedido entero (atomicidad).
        """
        resumen = {"aprobados": 0, "rechazados": 0}

        for pedido in self.pedidos_registrados:
            if pedido.estado != EstadoPedido.PENDIENTE:
                continue

            exito = self._procesar_cobro_pedido(pedido)
            if exito:
                pedido.marcar_estado(EstadoPedido.COBRADO)
                resumen["aprobados"] += 1
            else:
                pedido.marcar_estado(EstadoPedido.RECHAZADO)
                resumen["rechazados"] += 1

        return resumen

    def _procesar_cobro_pedido(self, pedido: Pedido) -> bool:
        """
        Intenta debitar los importes requeridos. Si se detecta falta de fondos
        en cualquiera de las cuentas involucradas, se aborta y se asegura
        el rechazo total (revirtiendo cualquier débito parcial).
        """
        if isinstance(pedido, PedidoSimple):
            monto = pedido.calcular_total()
            try:
                pedido.cuenta_pago.debitar(monto)
                # Descontar stock de los productos
                for linea in pedido.lineas:
                    linea.producto.reservar_stock(linea.cantidad)
                return True
            except SaldoInsuficienteError as err:
                self.historial_rechazos.append({
                    "pedido": pedido.id_pedido,
                    "motivo": str(err)
                })
                return False

        elif isinstance(pedido, PedidoCompuesto):
            # Paso 1: Comprobar viabilidad atómica de todas las cuentas requeridas
            subpedidos_simples = self._aplanar_pedidos_simples(pedido)
            debitos_previstos: Dict[CuentaPago, float] = {}

            for sp in subpedidos_simples:
                cuenta = sp.cuenta_pago
                monto = sp.calcular_total()
                debitos_previstos[cuenta] = debitos_previstos.get(cuenta, 0.0) + monto

            # Verificar si cada cuenta posee fondos suficientes para la suma total asignada
            for cuenta, monto_total in debitos_previstos.items():
                if cuenta.saldo_disponible < monto_total:
                    self.historial_rechazos.append({
                        "pedido": pedido.id_pedido,
                        "motivo": (
                            f"Rechazo atómico de pedido compuesto: cuenta {cuenta.id_cuenta} requiere {monto_total:.2f} "
                            f"pero solo dispone de {cuenta.saldo_disponible:.2f}."
                        )
                    })
                    return False

            # Paso 2: Si todas las cuentas disponen de fondos, ejecutar el cobro y reserva de stock
            for cuenta, monto_total in debitos_previstos.items():
                cuenta.debitar(monto_total)

            for sp in subpedidos_simples:
                for linea in sp.lineas:
                    linea.producto.reservar_stock(linea.cantidad)

            return True

        return False

    def _aplanar_pedidos_simples(self, pedido: Pedido) -> List[PedidoSimple]:
        """Extrae recursivamente todos los pedidos simples contenidos."""
        simples: List[PedidoSimple] = []
        if isinstance(pedido, PedidoSimple):
            simples.append(pedido)
        elif isinstance(pedido, PedidoCompuesto):
            for hijo in pedido.pedidos_hijos:
                simples.extend(self._aplanar_pedidos_simples(hijo))
        return simples

    def ordenar_distribucion(self, pedido: Pedido) -> None:
        """Transiciona un pedido cobrado hacia el estado de distribución."""
        if pedido.estado != EstadoPedido.COBRADO:
            raise ReglaNegocioError(
                f"No se puede distribuir el pedido {pedido.id_pedido} en estado '{pedido.estado.value}'. "
                f"Debe estar en '{EstadoPedido.COBRADO.value}'."
            )
        pedido.marcar_estado(EstadoPedido.EN_DISTRIBUCION)

    def confirmar_entrega(self, pedido: Pedido) -> None:
        """Confirma la entrega del pedido servido."""
        if pedido.estado != EstadoPedido.EN_DISTRIBUCION:
            raise ReglaNegocioError(
                f"No se puede confirmar el pedido {pedido.id_pedido} en estado '{pedido.estado.value}'. "
                f"Debe estar en '{EstadoPedido.EN_DISTRIBUCION.value}'."
            )
        pedido.marcar_estado(EstadoPedido.CONFIRMADO)
