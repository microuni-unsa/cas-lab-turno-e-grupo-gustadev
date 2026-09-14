"""
Módulo de catálogo y stock de productos.
Caso de Estudio 3 - Laboratorio 3 (Calidad de Software).
"""


class StockInsuficienteError(Exception):
    """Excepción cuando se solicita una cantidad superior al stock disponible."""
    pass


class Producto:
    """Representa un producto comercializable con control de existencias."""

    def __init__(self, codigo: str, nombre: str, precio_unitario: float, stock: int):
        self.codigo = codigo
        self.nombre = nombre
        self.precio_unitario = float(precio_unitario)
        self.stock = int(stock)

    def reservar_stock(self, cantidad: int) -> None:
        """Reserva y descuenta unidades del stock en almacén."""
        if cantidad <= 0:
            raise ValueError("La cantidad solicitada debe ser positiva.")
        if cantidad > self.stock:
            raise StockInsuficienteError(
                f"Stock insuficiente para producto '{self.nombre}' (código {self.codigo}). "
                f"Disponible: {self.stock}, Solicitado: {cantidad}"
            )
        self.stock -= cantidad

    def reponer_stock(self, cantidad: int) -> None:
        """Devuelve o adiciona existencias al inventario."""
        if cantidad <= 0:
            raise ValueError("La cantidad a reponer debe ser positiva.")
        self.stock += cantidad

    def __repr__(self) -> str:
        return f"Producto(codigo={self.codigo}, nombre={self.nombre}, stock={self.stock})"


class LineaPedido:
    """Representa el detalle de un ítem dentro de un pedido simple."""

    def __init__(self, producto: Producto, cantidad: int):
        if cantidad <= 0:
            raise ValueError("La cantidad debe ser mayor a cero.")
        if cantidad > producto.stock:
            raise StockInsuficienteError(
                f"No es posible añadir {cantidad} unidades. Stock disponible: {producto.stock}"
            )
        self.producto = producto
        self.cantidad = cantidad

    @property
    def subtotal(self) -> float:
        """Calcula el subtotal monetario de la línea."""
        return self.producto.precio_unitario * self.cantidad

    def __repr__(self) -> str:
        return f"LineaPedido(producto={self.producto.codigo}, cant={self.cantidad}, subtotal={self.subtotal:.2f})"
