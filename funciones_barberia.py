"""
Funciones para la gestión de la barbería Hairlook.
Conexión a base de datos MySQL/MariaDB usando pymysql.
"""

import pymysql
from typing import List, Dict, Optional, Tuple


def get_connection() -> pymysql.connections.Connection:
    """
    Crea y retorna una conexión a la base de datos.
    Configura los parámetros de conexión según el entorno.
    """
    connection = pymysql.connect(
        host='localhost',
        user='root',
        password='',
        database='hairlook',
        charset='utf8mb4',
        cursorclass=pymysql.cursors.DictCursor
    )
    return connection


def crear_cita(
    id_usuario: int,
    id_profesional: int,
    fecha: str,
    hora: str,
    tipo: str = 'local'
) -> Optional[int]:
    """
    Crea una nueva cita en la base de datos.

    Args:
        id_usuario: ID del cliente/usuario
        id_profesional: ID del barbero/profesional
        fecha: Fecha de la cita (formato 'YYYY-MM-DD')
        hora: Hora de la cita (formato 'HH:MM')
        tipo: Tipo de servicio ('local' o 'domicilio')

    Returns:
        ID de la cita creada o None si falla
    """
    connection = None
    try:
        connection = get_connection()
        cursor = connection.cursor()

        # Combinar fecha y hora en un solo datetime
        fecha_hora = f"{fecha} {hora}"

        # Determinar estado inicial según el tipo
        estado = 'programada' if tipo == 'domicilio' else 'confirmada'

        # Query para insertar la cita
        query = """
            INSERT INTO cita (Fecha_hora, Estado, ID_Usuario, ID_Profesional)
            VALUES (%s, %s, %s, %s)
        """
        cursor.execute(query, (fecha_hora, estado, id_usuario, id_profesional))
        connection.commit()

        # Obtener el ID de la cita creada
        cita_id = cursor.lastrowid
        return cita_id

    except pymysql.MySQLError as e:
        print(f"Error al crear la cita: {e}")
        if connection:
            connection.rollback()
        return None
    finally:
        if connection:
            connection.close()


def obtener_agenda_barbero(id_profesional: int, solo_pendientes: bool = True) -> List[Dict]:
    """
    Obtiene las citas de un profesional, filtrando por estado si es necesario.

    Args:
        id_profesional: ID del barbero/profesional
        solo_pendientes: Si True, filtra solo citas confirmadas/pendientes

    Returns:
        Lista de diccionarios con información de cada cita
    """
    connection = None
    try:
        connection = get_connection()
        cursor = connection.cursor()

        # Query base para obtener citas con información del cliente
        query = """
            SELECT c.ID_Cita, c.Fecha_hora, c.Estado,
                   u.Nombre as Cliente, u.Teléfono as Telefono_cliente,
                   c.ID_Usuario
            FROM cita c
            JOIN usuario u ON c.ID_Usuario = u.ID_Usuario
            WHERE c.ID_Profesional = %s
        """
        params = [id_profesional]

        # Agregar filtro de estado si se solicitan solo pendientes
        if solo_pendientes:
            query += " AND c.Estado IN ('confirmada', 'programada')"
        query += " ORDER BY c.Fecha_hora ASC"

        cursor.execute(query, params)
        citas = cursor.fetchall()

        # Para cada cita, obtener los servicios asociados
        for cita in citas:
            query_servicios = """
                SELECT s.Nombre, dc.Precio_aplicado
                FROM detalle_cita dc
                JOIN servicio s ON dc.ID_Servicio = s.ID_Servicio
                WHERE dc.ID_Cita = %s
            """
            cursor.execute(query_servicios, (cita['ID_Cita'],))
            cita['Servicios'] = cursor.fetchall()

        return citas

    except pymysql.MySQLError as e:
        print(f"Error al obtener la agenda: {e}")
        return []
    finally:
        if connection:
            connection.close()


def buscar_barberos_por_ubicacion(
    latitud: Optional[float] = None,
    longitud: Optional[float] = None,
    ciudad: Optional[str] = None,
    radio_km: float = 10.0
) -> List[Dict]:
    """
    Busca barberos por ubicación. Simula un mapa de búsqueda.

    Nota: Esta función es simulada ya que la tabla profesional no tiene
    columnas de latitud/longitud en el esquema actual. Se podría extender
    agregando estas columnas o usando un campo de ciudad.

    Args:
        latitud: Latitud del punto de búsqueda
        longitud: Longitud del punto de búsqueda
        ciudad: Nombre de la ciudad (alternativa a coordenadas)
        radio_km: Radio de búsqueda en kilómetros

    Returns:
        Lista de profesionales disponibles
    """
    connection = None
    try:
        connection = get_connection()
        cursor = connection.cursor()

        # Query base - filtrar por ciudad si se proporciona
        # NOTA: Esta es una simulación. En producción, se necesitarían
        # columnas de geolocalización en la tabla profesional

        query = """
            SELECT p.ID_Profesional, p.Nombre, p.Especialidad,
                   p.Telefono, p.Correo
            FROM profesional p
            WHERE 1=1
        """
        params = []

        # Filtro simulado por ciudad (asumiendo que existe el campo)
        # En esquema real, se compararía distancia geográfica
        if ciudad:
            query += " AND /* Filtro por ciudad: " + ciudad + " */ 1=1"

        query += " ORDER BY p.Nombre ASC"

        cursor.execute(query, params)
        barberos = cursor.fetchall()

        # Agregar información de disponibilidad para cada barbero
        for barbero in barberos:
            query_disp = """
                SELECT Dia_semana, Hora_inicial, Hora_fin
                FROM disponibilidad
                WHERE ID_Profesional = %s
                ORDER BY FIELD(Dia_semana, 'Lunes', 'Martes', 'Miércoles',
                              'Jueves', 'Viernes', 'Sábado', 'Domingo')
            """
            cursor.execute(query_disp, (barbero['ID_Profesional'],))
            barbero['Disponibilidad'] = cursor.fetchall()

        return barberos

    except pymysql.MySQLError as e:
        print(f"Error al buscar barberos: {e}")
        return []
    finally:
        if connection:
            connection.close()


# Funciones auxiliares adicionales

def cancelar_cita(id_cita: int) -> bool:
    """
    Cancela una cita existente, cambiando su estado a 'cancelada'.

    Args:
        id_cita: ID de la cita a cancelar

    Returns:
        True si se canceló correctamente, False en caso contrario
    """
    connection = None
    try:
        connection = get_connection()
        cursor = connection.cursor()

        query = "UPDATE cita SET Estado = 'cancelada' WHERE ID_Cita = %s"
        cursor.execute(query, (id_cita,))
        connection.commit()

        return cursor.rowcount > 0

    except pymysql.MySQLError as e:
        print(f"Error al cancelar la cita: {e}")
        if connection:
            connection.rollback()
        return False
    finally:
        if connection:
            connection.close()


def obtener_servicios() -> List[Dict]:
    """
    Obtiene la lista de servicios disponibles.

    Returns:
        Lista de servicios con nombre, descripción, precio y duración
    """
    connection = None
    try:
        connection = get_connection()
        cursor = connection.cursor()

        query = """
            SELECT ID_Servicio, Nombre, Descripción, Precio, Duracion_min
            FROM servicio
            ORDER BY Precio ASC
        """
        cursor.execute(query)
        return cursor.fetchall()

    except pymysql.MySQLError as e:
        print(f"Error al obtener servicios: {e}")
        return []
    finally:
        if connection:
            connection.close()


def agregar_servicio_a_cita(id_cita: int, id_servicio: int, precio: float) -> bool:
    """
    Agrega un servicio a una cita existente.

    Args:
        id_cita: ID de la cita
        id_servicio: ID del servicio
        precio: Precio aplicado del servicio

    Returns:
        True si se agregó correctamente
    """
    connection = None
    try:
        connection = get_connection()
        cursor = connection.cursor()

        query = """
            INSERT INTO detalle_cita (ID_Cita, ID_Servicio, Precio_aplicado)
            VALUES (%s, %s, %s)
        """
        cursor.execute(query, (id_cita, id_servicio, precio))
        connection.commit()

        return True

    except pymysql.MySQLError as e:
        print(f"Error al agregar servicio: {e}")
        if connection:
            connection.rollback()
        return False
    finally:
        if connection:
            connection.close()