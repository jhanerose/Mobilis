import pymysql
from typing import Dict, Any, List, Optional
from config import DB_HOST, DB_PORT, DB_NAME, DB_USER, DB_PASS

def get_connection():
    """Get database connection"""
    return pymysql.connect(
        host=DB_HOST,
        port=DB_PORT,
        user=DB_USER,
        password=DB_PASS,
        database=DB_NAME,
        cursorclass=pymysql.cursors.DictCursor
    )

def execute_query(query: str, params: tuple = ()) -> List[Dict[str, Any]]:
    """Execute a SELECT query and return results"""
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute(query, params)
            result = cursor.fetchall()
            return result
    except Exception as e:
        print(f"Query error: {e}")
        print(f"Query: {query}")
        print(f"Params: {params}")
        return []
    finally:
        conn.close()

def execute_query_one(query: str, params: tuple = ()) -> Optional[Dict[str, Any]]:
    """Execute a SELECT query and return single result"""
    conn = get_connection()
    try:
        with conn.cursor() as cursor:
            cursor.execute(query, params)
            result = cursor.fetchone()
            return result
    except Exception as e:
        print(f"Query error: {e}")
        return None
    finally:
        conn.close()
