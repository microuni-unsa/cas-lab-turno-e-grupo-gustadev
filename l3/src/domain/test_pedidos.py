"""
Suite de pruebas unitarias para el modelo de dominio de Gestión de Pedidos.
Caso de Estudio 3 - Laboratorio 3 (Calidad de Software).
Verifica formalmente las invariantes de negocio, patrones Composite y Singleton.
"""

import unittest
from l3.src.domain.cliente import Cliente, CuentaPago, TarjetaCredito, SaldoInsuficienteError
from l3.src.domain.producto import Producto, LineaPedido, StockInsuficienteError
from l3.src.domain.pedido import (
    PedidoSimple,
    PedidoCompuesto,
    EstadoPedido,
    ReglaNegocioError
)
from l3.src.domain.gestor_cobro import GestorProcesamientoPedidos


class TestGestionPedidosDominio(unittest.TestCase):

    def setUp(self):
        GestorProcesamientoPedidos.reiniciar_instancia()
        self.gestor = GestorProcesamientoPedidos()

        # Configuración de Cliente y Cuentas
        self.cliente = Cliente(
            id_cliente="CLI-001",
            nombre="Luis Gustavo Sequeiros",
            direccion="Av. Independencia s/n, Arequipa",
            telefono="+51 954123456",
            email="lsequeiros@unsa.edu.pe"
        )
        self.tarjeta_a = TarjetaCredito("4557880011223344", "Luis Sequeiros", "12/28")
        self.tarjeta_b = TarjetaCredito("5412339988776655", "Luis Sequeiros", "06/29")
        self.cuenta_1 = CuentaPago("CTA-01", self.tarjeta_a, saldo_inicial=500.0)
        self.cuenta_2 = CuentaPago("CTA-02", self.tarjeta_b, saldo_inicial=200.0)
        self.cliente.agregar_cuenta(self.cuenta_1)
        self.cliente.agregar_cuenta(self.cuenta_2)

        # Configuración de Productos y Stock
        self.prod_laptop = Producto("PRD-01", "Laptop Ultrabook", precio_unitario=150.0, stock=50)
        self.prod_mouse = Producto("PRD-02", "Mouse Inalámbrico", precio_unitario=25.0, stock=100)
        self.prod_teclado = Producto("PRD-03", "Teclado Mecánico", precio_unitario=40.0, stock=10)

    def test_01_cliente_puede_ordenar_solo_con_dinero_disponible(self):
        """Verifica que un cliente solo pueda iniciar pedidos si tiene cuentas con saldo."""
        self.assertTrue(self.cliente.tiene_cuenta_con_dinero())

        cliente_moroso = Cliente("CLI-002", "Carlos Pérez", "Calle Lima 123", "987654321", "cperez@mail.com")
        cuenta_vacia = CuentaPago("CTA-03", self.tarjeta_a, saldo_inicial=0.0)
        cliente_moroso.agregar_cuenta(cuenta_vacia)
        self.assertFalse(cliente_moroso.tiene_cuenta_con_dinero())

    def test_02_pedido_simple_limite_maximo_20_unidades(self):
        """Valida que un pedido simple no admita más de 20 unidades en total."""
        pedido_simple = PedidoSimple("PED-SMP-01", self.cliente, self.cuenta_1)
        pedido_simple.agregar_linea(self.prod_laptop, 5)
        pedido_simple.agregar_linea(self.prod_mouse, 15)
        self.assertEqual(pedido_simple.total_unidades(), 20)

        # Intentar exceder el límite de 20 unidades
        with self.assertRaises(ReglaNegocioError):
            pedido_simple.agregar_linea(self.prod_teclado, 1)

    def test_03_pedido_compuesto_requiere_cuentas_del_mismo_cliente(self):
        """Valida que todos los pedidos simples dentro de uno compuesto pertenezcan al mismo cliente."""
        otro_cliente = Cliente("CLI-003", "Ana Gómez", "Calle Mercaderes 45", "999888777", "agomez@mail.com")
        cuenta_otra = CuentaPago("CTA-04", self.tarjeta_b, saldo_inicial=300.0)
        otro_cliente.agregar_cuenta(cuenta_otra)

        pedido_propio = PedidoSimple("PED-S1", self.cliente, self.cuenta_1)
        pedido_propio.agregar_linea(self.prod_mouse, 2)

        pedido_ajeno = PedidoSimple("PED-S2", otro_cliente, cuenta_otra)
        pedido_ajeno.agregar_linea(self.prod_mouse, 1)

        compuesto = PedidoCompuesto("PED-CMP-01", self.cliente)
        compuesto.agregar_subpedido(pedido_propio)

        with self.assertRaises(ReglaNegocioError):
            compuesto.agregar_subpedido(pedido_ajeno)

    def test_04_pedido_compuesto_minimo_dos_pedidos_hijos(self):
        """Valida que un pedido compuesto no sea válido con menos de 2 pedidos hijos."""
        compuesto = PedidoCompuesto("PED-CMP-02", self.cliente)
        pedido_s1 = PedidoSimple("PED-S1", self.cliente, self.cuenta_1)
        pedido_s1.agregar_linea(self.prod_mouse, 2)
        compuesto.agregar_subpedido(pedido_s1)

        with self.assertRaises(ReglaNegocioError):
            compuesto.validar_invariantes()

    def test_05_verificacion_estricta_de_stock(self):
        """Valida que no se puedan solicitar productos que excedan el stock disponible."""
        with self.assertRaises(StockInsuficienteError):
            LineaPedido(self.prod_teclado, cantidad=15)  # Solo hay 10 en stock

    def test_06_patron_singleton_gestor_cobro(self):
        """Valida que la clase responsable del cobro posea una única instancia en toda la aplicación."""
        gestor_a = GestorProcesamientoPedidos()
        gestor_b = GestorProcesamientoPedidos()
        self.assertIs(gestor_a, gestor_b)

    def test_07_ciclo_completo_cobro_distribucion_y_confirmacion(self):
        """Valida la transición de estados completa: PENDIENTE -> COBRADO -> DISTRIBUCION -> CONFIRMADO."""
        ps1 = PedidoSimple("PED-S1", self.cliente, self.cuenta_1)
        ps1.agregar_linea(self.prod_mouse, 4)  # 4 * 25 = 100.0

        ps2 = PedidoSimple("PED-S2", self.cliente, self.cuenta_2)
        ps2.agregar_linea(self.prod_teclado, 2)  # 2 * 40 = 80.0

        compuesto = PedidoCompuesto("PED-CMP-FULL", self.cliente)
        compuesto.agregar_subpedido(ps1)
        compuesto.agregar_subpedido(ps2)

        self.gestor.registrar_pedido(compuesto)
        self.assertEqual(compuesto.estado, EstadoPedido.PENDIENTE)

        # Ejecución del lote diario de cobro
        resumen = self.gestor.ejecutar_cobro_diario()
        self.assertEqual(resumen["aprobados"], 1)
        self.assertEqual(resumen["rechazados"], 0)
        self.assertEqual(compuesto.estado, EstadoPedido.COBRADO)
        self.assertEqual(self.cuenta_1.saldo_disponible, 400.0)  # 500 - 100
        self.assertEqual(self.cuenta_2.saldo_disponible, 120.0)  # 200 - 80

        # Ordenar distribución
        self.gestor.ordenar_distribucion(compuesto)
        self.assertEqual(compuesto.estado, EstadoPedido.EN_DISTRIBUCION)

        # Confirmación de entrega
        self.gestor.confirmar_entrega(compuesto)
        self.assertEqual(compuesto.estado, EstadoPedido.CONFIRMADO)

    def test_08_rechazo_atomico_de_pedido_compuesto_por_fondos_insuficientes(self):
        """
        Regla crítica de negocio: Si una cuenta no tiene suficiente dinero,
        el pedido se rechaza (si es parte de un pedido compuesto, se rechaza el pedido entero).
        """
        # La cuenta 2 tiene 200.0. Crearemos un pedido por 300.0 en esa cuenta.
        ps_viable = PedidoSimple("PED-OK", self.cliente, self.cuenta_1)
        ps_viable.agregar_linea(self.prod_mouse, 2)  # 50.0 (viable en cuenta 1)

        ps_sin_fondos = PedidoSimple("PED-FAIL", self.cliente, self.cuenta_2)
        ps_sin_fondos.agregar_linea(self.prod_teclado, 7)  # 7 * 40 = 280.0 > 200.0 (no viable)

        compuesto = PedidoCompuesto("PED-CMP-FAIL", self.cliente)
        compuesto.agregar_subpedido(ps_viable)
        compuesto.agregar_subpedido(ps_sin_fondos)

        self.gestor.registrar_pedido(compuesto)
        resumen = self.gestor.ejecutar_cobro_diario()

        self.assertEqual(resumen["rechazados"], 1)
        self.assertEqual(resumen["aprobados"], 0)
        self.assertEqual(compuesto.estado, EstadoPedido.RECHAZADO)
        # Verificar atomicidad: ninguna cuenta debe haber sido debitada
        self.assertEqual(self.cuenta_1.saldo_disponible, 500.0)
        self.assertEqual(self.cuenta_2.saldo_disponible, 200.0)


if __name__ == "__main__":
    unittest.main()
