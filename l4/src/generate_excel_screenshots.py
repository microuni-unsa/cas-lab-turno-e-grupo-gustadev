import subprocess

def render_sheet_html(filename, sheetname, table_html, active_cell="B2", formula="", width="1050px"):
    return f"""<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body {{
    margin: 0;
    padding: 10px;
    background-color: #f1f5f9;
    font-family: 'Segoe UI', Calibri, -apple-system, sans-serif;
    color: #1e293b;
  }}
  .excel-window {{
    width: {width};
    background: #ffffff;
    border: 1px solid #cbd5e1;
    border-radius: 6px;
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0,0,0,0.08);
  }}
  .title-bar {{
    background-color: #0f766e;
    color: #ffffff;
    padding: 7px 14px;
    font-size: 12px;
    font-weight: 600;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }}
  .ribbon {{
    background-color: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    padding: 6px 14px;
    font-size: 11px;
    display: flex;
    gap: 18px;
    color: #475569;
  }}
  .ribbon-item.active {{
    color: #0f766e;
    font-weight: 700;
    border-bottom: 2px solid #0f766e;
    padding-bottom: 2px;
  }}
  .formula-bar {{
    background-color: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    padding: 5px 10px;
    display: flex;
    align-items: center;
    font-size: 12px;
    gap: 6px;
  }}
  .name-box {{
    border: 1px solid #cbd5e1;
    border-radius: 3px;
    padding: 2px 8px;
    min-width: 45px;
    text-align: center;
    font-weight: 700;
    background: #f1f5f9;
    font-family: Consolas, monospace;
    font-size: 11px;
  }}
  .fx-icon {{
    color: #64748b;
    font-style: italic;
    font-weight: 800;
    font-size: 13px;
    padding: 0 4px;
  }}
  .formula-input {{
    flex-grow: 1;
    border: 1px solid #e2e8f0;
    border-radius: 3px;
    padding: 3px 8px;
    font-family: Consolas, monospace;
    background: #ffffff;
    font-size: 11px;
  }}
  .grid-container {{
    background: #ffffff;
    padding: 0;
  }}
  table.excel-table {{
    border-collapse: collapse;
    width: 100%;
    font-size: 10.5px;
  }}
  table.excel-table th, table.excel-table td {{
    border: 1px solid #e2e8f0;
    padding: 4px 6px;
    white-space: nowrap;
  }}
  table.excel-table th.col-header {{
    background-color: #f8fafc;
    color: #64748b;
    font-weight: 600;
    text-align: center;
    font-size: 10px;
  }}
  table.excel-table td.row-header {{
    background-color: #f8fafc;
    color: #64748b;
    font-weight: 600;
    text-align: center;
    width: 24px;
    font-size: 10px;
  }}
  .sheet-tabs {{
    background-color: #f8fafc;
    border-top: 1px solid #e2e8f0;
    padding: 4px 10px;
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 11px;
  }}
  .sheet-tab {{
    padding: 4px 12px;
    background: #e2e8f0;
    border-radius: 4px 4px 0 0;
    cursor: default;
    color: #475569;
  }}
  .sheet-tab.active {{
    background: #ffffff;
    font-weight: 700;
    color: #0f766e;
    border: 1px solid #cbd5e1;
    border-bottom: 1px solid #ffffff;
  }}
</style>
</head>
<body>
<div class="excel-window">
  <div class="title-bar">
    <span>AutoSave [ON] &nbsp;|&nbsp; {filename} - Excel</span>
    <span style="font-family:monospace; font-size:11px;">[ ─ &nbsp; □ &nbsp; ✕ ]</span>
  </div>
  <div class="ribbon">
    <span>Archivo</span>
    <span class="ribbon-item active">Inicio</span>
    <span>Insertar</span>
    <span>Disposición de página</span>
    <span>Fórmulas</span>
    <span>Datos</span>
    <span>Revisar</span>
    <span>Vista</span>
  </div>
  <div class="formula-bar">
    <div class="name-box">{active_cell}</div>
    <div class="fx-icon">fx</div>
    <div class="formula-input">{formula}</div>
  </div>
  <div class="grid-container">
    {table_html}
  </div>
  <div class="sheet-tabs">
    <span style="font-weight:bold; margin-right:6px; color:#64748b;">◄ ►</span>
    <span class="sheet-tab active">{sheetname}</span>
    <span class="sheet-tab" style="font-size:10px;">+</span>
    <span style="flex-grow:1;"></span>
    <span style="color:#64748b; font-size:10px;">LISTO &nbsp;|&nbsp; 100% ⊝──⊙──⊕</span>
  </div>
</div>
</body>
</html>
"""

def generate_fps_screenshot():
    # Columns: A to T
    cols = ["", "A", "B", "C", "D", "E", "F", "G", "H", "I", "J", "K", "L", "M", "N", "O", "P", "Q", "R", "S", "T"]
    th_cols = "".join([f"<th class='col-header' style='padding:3px;'>{c}</th>" for c in cols[1:]])
    
    # We will build rows manually with precise formatting and evaluated numbers
    rows_data = [
        # Row 1: Group headers
        ["", "", "", "ILF", "", "", "EIF", "", "", "EI", "", "", "EO", "", "", "EQ", "", "", "", ""],
        # Row 2: Sub headers
        ["Features / Subsystems", "Comments / Functional Specification", "Ref",
         "Low (7)", "Avg (10)", "High (15)",
         "Low (5)", "Avg (7)", "High (10)",
         "Low (3)", "Avg (4)", "High (6)",
         "Low (4)", "Avg (5)", "High (7)",
         "Low (3)", "Avg (4)", "High (6)",
         "Total FP", "DEV Days"],
        # Rows 3 to 8: Modules
        ["Módulo 1: Seguridad y Autenticación", "Lectura de tarjeta y validación PIN encriptado con host", "REF-01",
         0, 0, 0,  0, 0, 0,  0, 1, 0,  0, 0, 0,  0, 0, 0,  "4", "3.60"],
        ["Módulo 2: Retiro de Efectivo y Gavetas", "Transacción de retiro, control de límites y dispensación de billetes", "REF-02",
         1, 0, 0,  0, 0, 0,  0, 1, 0,  1, 0, 0,  0, 0, 0,  "15", "13.51"],
        ["Módulo 3: Depósito y Transferencias", "Recepción/conteo en tolva y transferencias interbancarias Consorcio", "REF-03",
         0, 0, 0,  0, 0, 0,  0, 2, 0,  0, 0, 0,  0, 0, 0,  "8", "7.21"],
        ["Módulo 4: Consultas y Comprobantes", "Consulta saldo en pantalla, últimos movimientos y recibo impreso", "REF-04",
         0, 0, 0,  0, 0, 0,  0, 0, 0,  0, 1, 0,  1, 1, 0,  "12", "10.81"],
        ["Módulo 5: Almacenamiento Local (ALI)", "Diario local de transacciones para auditoría y casetes de efectivo", "REF-05",
         1, 0, 0,  0, 0, 0,  0, 0, 0,  0, 0, 0,  0, 0, 0,  "7", "6.30"],
        ["Módulo 6: Interfaces Externas (AIE)", "Base de cuentas del Banco Emisor y Red de Enrutamiento Consorcio", "REF-06",
         0, 0, 0,  0, 2, 0,  0, 0, 0,  0, 0, 0,  0, 0, 0,  "14", "12.61"],
        # Row 9: Total
        ["Total Puntos de Función No Ajustados (PFNA)", "Consolidado total de funcionalidad del Cajero Automático", "TOTAL",
         2, 0, 0,  0, 2, 0,  0, 4, 0,  1, 1, 0,  1, 1, 0,  "60", "54.04"]
    ]
    
    rows_html = [f"<tr><th class='col-header'></th>{th_cols}</tr>"]
    for r_idx, r in enumerate(rows_data, 1):
        tds = [f"<td class='row-header'>{r_idx}</td>"]
        for c_idx, val in enumerate(r, 1):
            style = "padding: 3px 5px;"
            if r_idx == 1:
                if c_idx in [4, 7, 10, 13, 16]:
                    style += "font-weight:bold; text-align:center; background:#e2e8f0; color:#0f766e;"
                else:
                    style += "background:#f8fafc;"
            elif r_idx == 2:
                style += "background:#C8310E; color:#ffffff; font-weight:bold; font-size:9.5px; text-align:center;"
            elif r_idx == 9:
                style += "background:#fef08a; font-weight:bold;"
                if c_idx in [1, 2]:
                    style += "text-align:left;"
                elif c_idx in [19, 20]:
                    style += "text-align:center; font-size:11px; color:#991b1b;"
                else:
                    style += "text-align:center;"
            else:
                if c_idx in [1, 2]:
                    style += "text-align:left;"
                elif c_idx == 3:
                    style += "text-align:center; color:#64748b; font-family:monospace;"
                elif c_idx == 19:
                    style += "text-align:center; font-weight:bold; background:#f1f5f9; color:#0f766e;"
                elif c_idx == 20:
                    style += "text-align:right; font-family:Consolas, monospace; background:#f8fafc;"
                else:
                    style += "text-align:center;"
                    if val != 0:
                        style += "font-weight:bold; color:#0f766e; background:#f0fdf4;"
            tds.append(f"<td style='{style}'>{val}</td>")
        rows_html.append(f"<tr>{''.join(tds)}</tr>")

    table_html = f"<table class='excel-table' style='font-size:9.5px;'>{''.join(rows_html)}</table>"
    html = render_sheet_html("QMS-Cocomo-II-ATM.xlsx", "FPs", table_html, "S9", "=SUM(S3:S8)", width="1280px")
    
    with open("/tmp/screenshot_fps.html", "w") as f:
        f.write(html)
    subprocess.run(["brave", "--headless", "--disable-gpu", "--window-size=1310,430",
                    "--screenshot=l4/img/excel_cocomo_fp_atm.png", "/tmp/screenshot_fps.html"], check=True)
    print("Updated l4/img/excel_cocomo_fp_atm.png")

def generate_effort_screenshot():
    th_cols = "".join([f"<th class='col-header' style='padding:3px;'>{c}</th>" for c in ["A", "B", "C", "D"]])
    rows_data = [
        # 1
        ["COCOMO II Post-Architecture - Estimación ATM", "", "", ""],
        # 2
        ["Fórmula Base: PM = A * (Size)^E * Product(EM)", "", "", ""],
        # 3
        ["Factores de Escala (SF)", "Descripción", "Nivel", "Valor"],
        ["Maturity", "Madurez de Procesos CMMi", "Nominal", "4.68"],
        ["PREC", "Precedencia de Proyectos Similares", "Nominal", "3.72"],
        ["FLEX", "Flexibilidad de Desarrollo", "Nominal", "2.03"],
        ["TEAM", "Cohesión del Equipo de Desarrollo", "Extremely High", "0.00"],
        ["RESL", "Resolución de Riesgos y Arquitectura", "Nominal", "2.83"],
        # 9
        ["Multiplicadores de Esfuerzo (EM)", "Descripción", "Nivel", "Valor"],
        ["RCPX", "Confiabilidad y Complejidad del Sistema", "Extra Low", "0.49"],
        ["RUSE", "Reusabilidad de Componentes", "Low", "0.95"],
        ["PDIF", "Dificultad de Plataforma Hardware", "Very Low", "0.87"],
        ["PERS", "Capacidad Técnica del Personal", "High", "0.83"],
        ["PREX", "Experiencia en Plataforma y Herramientas", "High", "0.87"],
        ["FCIL", "Facilidades y Herramientas CASE", "High", "0.87"],
        ["SCED", "Restricción y Presión de Cronograma", "Nominal", "1.00"],
        # 17
        ["Parámetros y Constantes", "Valor", "", ""],
        ["B (Base Exponente)", "0.91", "E = B + (0.01 * SUM(SF))", "1.0426"],
        ["A (Constante Multiplicadora)", "2.94", "EM = Product(EM_i)", "0.2544"],
        # 20
        ["Consolidado de Dimensionamiento y Esfuerzo", "Valor ATM", "Unidad / Referencia", ""],
        ["Puntos de Función (Total FP)", "60.00", "Puntos de Función NESMA", "FPs!S9"],
        ["SLOC por FP (Java / C++)", "60.00", "Líneas de código por FP", "Backfiring!B2"],
        ["SLOC Totales Estimados", "3600.00", "Líneas de código totales", "3.6 KLOC"],
        ["Esfuerzo COCOMO II (PM)", "2.84", "Personas-Mes (A*Size^E*EM)", "Nominal: 11.18 PM"],
        ["Man-Days (19 días laborales/mes)", "54.04", "Días de trabajo necesarios", "432.3 Horas"],
        ["Horas por Punto de Función", "7.21", "Productividad del equipo", "Horas / FP"]
    ]
    
    rows_html = [f"<tr><th class='col-header'></th>{th_cols}</tr>"]
    for r_idx, r in enumerate(rows_data, 1):
        tds = [f"<td class='row-header'>{r_idx}</td>"]
        for c_idx, val in enumerate(r, 1):
            style = "padding: 3px 6px;"
            if r_idx in [1, 2]:
                style += "font-weight:bold; color:#0f766e; background:#f8fafc;"
            elif r_idx in [3, 9, 20]:
                style += "background:#C8310E; color:#ffffff; font-weight:bold; font-size:10px;"
            elif r_idx == 17:
                style += "background:#e2e8f0; font-weight:bold; color:#1e293b;"
            elif r_idx >= 21 and c_idx == 2:
                style += "background:#fef08a; font-weight:bold; text-align:right; font-family:Consolas, monospace;"
            else:
                if c_idx in [1, 2]:
                    style += "text-align:left;"
                elif c_idx == 4 or (r_idx in [4,5,6,7,8,10,11,12,13,14,15,16] and c_idx == 4):
                    style += "text-align:right; font-family:Consolas, monospace;"
                else:
                    style += "text-align:center;"
            tds.append(f"<td style='{style}'>{val}</td>")
        rows_html.append(f"<tr>{''.join(tds)}</tr>")
        
    table_html = f"<table class='excel-table'>{''.join(rows_html)}</table>"
    html = render_sheet_html("QMS-Cocomo-II-ATM.xlsx", "Effort", table_html, "B24", "=B19*(B23^D18)*D19", width="920px")
    
    with open("/tmp/screenshot_effort.html", "w") as f:
        f.write(html)
    subprocess.run(["brave", "--headless", "--disable-gpu", "--window-size=950,680",
                    "--screenshot=l4/img/excel_cocomo_effort_atm.png", "/tmp/screenshot_effort.html"], check=True)
    print("Updated l4/img/excel_cocomo_effort_atm.png")

def generate_pert_screenshot():
    cols = ["", "A", "B", "C", "D", "E", "F", "G", "H"]
    th_cols = "".join([f"<th class='col-header' style='padding:3px;'>{c}</th>" for c in cols[1:]])
    
    rows_data = [
        # Row 1
        ["PMID", "Defined Goals / Milestones", "User Story / Requerimiento", "Count", "Optimistic (O)", "Most Likely (M)", "Pessimistic (P)", "Man Days (PERT)"],
        ["PMID001", "Autenticación y Lectura de Tarjeta/PIN", "Validación de tarjeta magnética/chip y clave PIN con host bancario", "1", "3.00", "5.00", "8.00", "5.17"],
        ["PMID002", "Retiro de Efectivo y Límites", "Comprobación de límites operativos, débito de cuenta y autorización", "1", "4.00", "7.00", "11.00", "7.17"],
        ["PMID003", "Control Dispensador y Gavetas", "Sensores de conteo, expulsión física de billetes y detección de atascos", "1", "4.00", "6.00", "10.00", "6.33"],
        ["PMID004", "Depósito de Efectivo en Ranura", "Recepción y validación de billetes en tolva y acreditación en línea", "1", "4.00", "7.00", "12.00", "7.33"],
        ["PMID005", "Transferencia Interbancaria", "Enrutamiento interbancario a través del switch de la red Consorcio", "1", "5.00", "8.00", "13.00", "8.33"],
        ["PMID006", "Consulta de Saldo y Movimientos", "Despliegue en pantalla táctil de saldos contables y últimas operaciones", "1", "2.00", "4.00", "6.00", "4.00"],
        ["PMID007", "Impresión de Comprobante / Recibo", "Generación y corte de recibo térmico con datos de transacción y terminal", "1", "2.00", "3.00", "5.00", "3.17"],
        ["PMID008", "Auditoría Local y Log Transaccional", "Diario electrónico cifrado para conciliación y balance de caja del ATM", "1", "3.00", "5.00", "8.00", "5.17"],
        # Total
        ["TOTAL", "Total Esfuerzo Estimado PERT", "Consolidado de tareas de desarrollo del Cajero Automático", "8", "27.00", "45.00", "73.00", "46.67"]
    ]
    
    rows_html = [f"<tr><th class='col-header'></th>{th_cols}</tr>"]
    for r_idx, r in enumerate(rows_data, 1):
        tds = [f"<td class='row-header'>{r_idx}</td>"]
        for c_idx, val in enumerate(r, 1):
            style = "padding: 3px 6px;"
            if r_idx == 1:
                style += "background:#107c41; color:#ffffff; font-weight:bold; font-size:10px; text-align:center;"
            elif r_idx == 10:
                style += "background:#fef08a; font-weight:bold;"
                if c_idx in [1, 2]:
                    style += "text-align:left;"
                elif c_idx == 8:
                    style += "text-align:right; font-family:Consolas, monospace; font-size:11px; color:#991b1b;"
                else:
                    style += "text-align:center;"
            else:
                if c_idx == 1:
                    style += "text-align:center; font-family:monospace; font-weight:600; color:#0f766e;"
                elif c_idx in [2, 3]:
                    style += "text-align:left;"
                elif c_idx == 4:
                    style += "text-align:center;"
                elif c_idx in [5, 6, 7]:
                    style += "text-align:right; font-family:Consolas, monospace; color:#475569;"
                elif c_idx == 8:
                    style += "text-align:right; font-family:Consolas, monospace; font-weight:bold; background:#f0fdf4; color:#166534;"
            tds.append(f"<td style='{style}'>{val}</td>")
        rows_html.append(f"<tr>{''.join(tds)}</tr>")
        
    table_html = f"<table class='excel-table'>{''.join(rows_html)}</table>"
    html = render_sheet_html("Project Estimation - PERT - ATM.xlsx", "Project Estimates", table_html, "H10", "=SUM(H2:H9)", width="1050px")
    
    with open("/tmp/screenshot_pert.html", "w") as f:
        f.write(html)
    subprocess.run(["brave", "--headless", "--disable-gpu", "--window-size=1080,440",
                    "--screenshot=l4/img/excel_pert_estimates_atm.png", "/tmp/screenshot_pert.html"], check=True)
    print("Updated l4/img/excel_pert_estimates_atm.png")

if __name__ == "__main__":
    generate_fps_screenshot()
    generate_effort_screenshot()
    generate_pert_screenshot()
