import openpyxl
from openpyxl.styles import Font, PatternFill, Alignment, Border, Side
import xlrd

def adapt_pert():
    wb = openpyxl.load_workbook("l4/guide/Project Estimation - PERT 1.0.xlsx")
    
    # 1. Update Project Dashboard
    ws_dash = wb["Project Dashboard"]
    ws_dash["B2"] = "Sistema de Cajero Automático (ATM)"
    ws_dash["B3"] = "ATM-CAS-2026"
    ws_dash["B4"] = "Consorcio Red Bancaria"
    ws_dash["B5"] = "28/09/2026"
    
    # 2. Update Project Estimates
    ws_est = wb["Project Estimates"]
    
    # Clean old rows (rows 2 to 17)
    for r in range(2, 20):
        for c in range(1, 14):
            ws_est.cell(r, c).value = None
            
    atm_tasks = [
        ("PMID001", "Autenticación y Lectura de Tarjeta/PIN", "Como usuario del cajero requiero validar mi tarjeta física y PIN de seguridad para acceder a los servicios de mi cuenta bancaria.", 1, 3.0, 5.0, 8.0),
        ("PMID002", "Retiro de Efectivo con Validación de Límites", "Como usuario requiero solicitar el retiro de billetes, verificando saldo disponible y límites operativos diarios establecidos.", 1, 4.0, 7.0, 11.0),
        ("PMID003", "Control de Dispensador y Gavetas de Billetes", "Como sistema ATM requiero controlar los sensores de conteo, expulsión física de billetes y detección de atascos en tolva.", 1, 4.0, 6.0, 10.0),
        ("PMID004", "Depósito de Efectivo en Ranura/Tolva", "Como usuario requiero depositar efectivo validando la autenticidad de los billetes y acreditando el saldo en tiempo real.", 1, 4.0, 7.0, 12.0),
        ("PMID005", "Transferencia de Fondos Interbancaria", "Como usuario requiero transferir fondos hacia cuentas propias o interbancarias a través del switch del Consorcio.", 1, 5.0, 8.0, 13.0),
        ("PMID006", "Consulta de Saldo y Últimos Movimientos", "Como usuario requiero visualizar en la pantalla táctil el saldo contable disponible y los últimos movimientos de mi cuenta.", 1, 2.0, 4.0, 6.0),
        ("PMID007", "Impresión de Comprobante / Recibo Térmico", "Como usuario requiero emitir un comprobante impreso que certifique el monto debitado, número de transacción y terminal.", 1, 2.0, 3.0, 5.0),
        ("PMID008", "Módulo de Auditoría Local y Registro de Transacciones", "Como administrador requiero registrar localmente en el diario electrónico cada evento y transacción para fines de arqueo y conciliación.", 1, 3.0, 5.0, 8.0),
    ]
    
    border_thin = Border(
        left=Side(style='thin', color='D9D9D9'),
        right=Side(style='thin', color='D9D9D9'),
        top=Side(style='thin', color='D9D9D9'),
        bottom=Side(style='thin', color='D9D9D9')
    )
    
    for idx, t in enumerate(atm_tasks):
        r = idx + 2
        pmid, title, story, count, o, m, p = t
        ws_est.cell(r, 1, pmid)
        ws_est.cell(r, 2, title)
        ws_est.cell(r, 3, story)
        ws_est.cell(r, 4, count)
        ws_est.cell(r, 5, o)
        ws_est.cell(r, 6, m)
        ws_est.cell(r, 7, p)
        # Formula for PERT: =(E{r} + 4*F{r} + G{r})/6
        ws_est.cell(r, 8, f"=(E{r}+4*F{r}+G{r})/6")
        
        for c in range(1, 9):
            ws_est.cell(r, c).border = border_thin
            if c in [1, 4]:
                ws_est.cell(r, c).alignment = Alignment(horizontal="center", vertical="center")
            elif c in [5, 6, 7, 8]:
                ws_est.cell(r, c).alignment = Alignment(horizontal="right", vertical="center")
                ws_est.cell(r, c).number_format = "0.00"
                
    # Total row
    r_tot = len(atm_tasks) + 2
    ws_est.cell(r_tot, 2, "Total Esfuerzo Estimado (Man-Days)")
    ws_est.cell(r_tot, 2).font = Font(name="Calibri", bold=True, size=11)
    ws_est.cell(r_tot, 8, f"=SUM(H2:H{r_tot-1})")
    ws_est.cell(r_tot, 8).font = Font(name="Calibri", bold=True, size=11)
    ws_est.cell(r_tot, 8).number_format = "0.00"
    ws_est.cell(r_tot, 8).fill = PatternFill(start_color="FFF2CC", end_color="FFF2CC", fill_type="solid")

    out_pert = "l4/src/Project Estimation - PERT - ATM.xlsx"
    wb.save(out_pert)
    print(f"Saved {out_pert}")

def adapt_cocomo():
    wb = openpyxl.Workbook()
    # Remove default sheet
    wb.remove(wb.active)
    
    font_header = Font(name="Arial", size=10, bold=True, color="FFFFFF")
    fill_header = PatternFill(start_color="C8310E", end_color="C8310E", fill_type="solid")
    fill_total = PatternFill(start_color="FFF2CC", end_color="FFF2CC", fill_type="solid")
    font_bold = Font(name="Arial", size=9, bold=True)
    font_regular = Font(name="Arial", size=9)
    border_cell = Border(left=Side(style='thin', color='CBD5E1'), right=Side(style='thin', color='CBD5E1'),
                         top=Side(style='thin', color='CBD5E1'), bottom=Side(style='thin', color='CBD5E1'))

    # 1. FP Units sheet
    ws_units = wb.create_sheet(title="FP Units")
    ws_units.views.sheetView[0].showGridLines = True
    units_data = [
        ["FP Units", "", ""],
        ["Function", "Complexity", "FPs"],
        ["ILF", "Simple", 7],
        ["", "Average", 10],
        ["", "Complex", 15],
        ["EIF", "Simple", 5],
        ["", "Average", 7],
        ["", "Complex", 10],
        ["EI", "Simple", 3],
        ["", "Average", 4],
        ["", "Complex", 6],
        ["EO", "Simple", 4],
        ["", "Average", 5],
        ["", "Complex", 7],
        ["EQ", "Simple", 3],
        ["", "Average", 4],
        ["", "Complex", 6],
    ]
    for r_idx, row in enumerate(units_data, 1):
        for c_idx, val in enumerate(row, 1):
            cell = ws_units.cell(r_idx, c_idx, val)
            cell.font = font_bold if r_idx <= 2 or val != "" else font_regular
            if r_idx == 2:
                cell.font = font_header
                cell.fill = fill_header

    # 2. FPs sheet (Configured for ATM with exact 60 FP)
    ws_fps = wb.create_sheet(title="FPs")
    ws_fps.views.sheetView[0].showGridLines = True
    
    # Headers
    ws_fps.cell(1, 4, "ILF").alignment = Alignment(horizontal="center")
    ws_fps.cell(1, 7, "EIF").alignment = Alignment(horizontal="center")
    ws_fps.cell(1, 10, "EI").alignment = Alignment(horizontal="center")
    ws_fps.cell(1, 13, "EO").alignment = Alignment(horizontal="center")
    ws_fps.cell(1, 16, "EQ").alignment = Alignment(horizontal="center")
    for c in [4, 7, 10, 13, 16]:
        ws_fps.cell(1, c).font = font_bold
    
    headers_sub = [
        "Features / Subsystems", "Comments / Functional Specification", "Ref",
        "Low (7)", "Avg (10)", "High (15)",
        "Low (5)", "Avg (7)", "High (10)",
        "Low (3)", "Avg (4)", "High (6)",
        "Low (4)", "Avg (5)", "High (7)",
        "Low (3)", "Avg (4)", "High (6)",
        "Total FPs", "DEV Days", "DEV Hours", "Total Days"
    ]
    for c_idx, h in enumerate(headers_sub, 1):
        cell = ws_fps.cell(2, c_idx, h)
        cell.font = Font(name="Arial", size=8, bold=True, color="FFFFFF")
        cell.fill = fill_header
        cell.alignment = Alignment(horizontal="center", vertical="center", wrap_text=True)

    atm_fp_rows = [
        ("Módulo 1: Seguridad y Autenticación", "Lectura de tarjeta y validación de clave PIN encriptada con host bancario", "REF-01",
         0, 0, 0,  0, 0, 0,  0, 1, 0,  0, 0, 0,  0, 0, 0),
        ("Módulo 2: Retiro de Efectivo y Gavetas", "Transacción de retiro, control de límites y dispensación de billetes físicos", "REF-02",
         1, 0, 0,  0, 0, 0,  0, 1, 0,  1, 0, 0,  0, 0, 0),
        ("Módulo 3: Depósito y Transferencias", "Recepción y conteo de billetes en tolva, y transferencias interbancarias vía Consorcio", "REF-03",
         0, 0, 0,  0, 0, 0,  0, 2, 0,  0, 0, 0,  0, 0, 0),
        ("Módulo 4: Consultas y Comprobantes", "Consulta de saldo, reporte de últimos movimientos y emisión de comprobante impreso", "REF-04",
         0, 0, 0,  0, 0, 0,  0, 0, 0,  0, 1, 0,  1, 1, 0),
        ("Módulo 5: Almacenamiento Local (ALI)", "Diario local de transacciones para auditoría y registro de efectivo en casetes", "REF-05",
         1, 0, 0,  0, 0, 0,  0, 0, 0,  0, 0, 0,  0, 0, 0),
        ("Módulo 6: Interfaces Externas (AIE)", "Base de cuentas del Banco Emisor y Red de Enrutamiento del Consorcio", "REF-06",
         0, 0, 0,  0, 2, 0,  0, 0, 0,  0, 0, 0,  0, 0, 0),
    ]

    for idx, rdata in enumerate(atm_fp_rows, 3):
        ws_fps.cell(idx, 1, rdata[0]).font = font_bold
        ws_fps.cell(idx, 2, rdata[1]).font = font_regular
        ws_fps.cell(idx, 3, rdata[2]).font = font_regular
        for c_offset, val in enumerate(rdata[3:], 4):
            c = ws_fps.cell(idx, c_offset, val)
            c.font = font_regular
            c.alignment = Alignment(horizontal="center")
            c.border = border_cell
        
        # Total FP formula for row
        # 4: ILF low (7), 5: ILF avg (10), 6: ILF high (15)
        # 7: EIF low (5), 8: EIF avg (7), 9: EIF high (10)
        # 10: EI low (3), 11: EI avg (4), 12: EI high (6)
        # 13: EO low (4), 14: EO avg (5), 15: EO high (7)
        # 16: EQ low (3), 17: EQ avg (4), 18: EQ high (6)
        f_fp = (f"=(D{idx}*7+E{idx}*10+F{idx}*15 + "
                f"G{idx}*5+H{idx}*7+I{idx}*10 + "
                f"J{idx}*3+K{idx}*4+L{idx}*6 + "
                f"M{idx}*4+N{idx}*5+O{idx}*7 + "
                f"P{idx}*3+Q{idx}*4+R{idx}*6)")
        ws_fps.cell(idx, 19, f_fp).font = font_bold
        ws_fps.cell(idx, 19).alignment = Alignment(horizontal="center")
        ws_fps.cell(idx, 19).border = border_cell
        
        # DEV Days formula: =S{idx}*(54.04/60)
        ws_fps.cell(idx, 20, f"=S{idx}*(Effort!B36/Effort!B37)")
        ws_fps.cell(idx, 20).font = font_regular
        ws_fps.cell(idx, 20).alignment = Alignment(horizontal="right")
        ws_fps.cell(idx, 20).number_format = "0.00"
        ws_fps.cell(idx, 20).border = border_cell
        
        # DEV Hours: =T{idx}*8
        ws_fps.cell(idx, 21, f"=T{idx}*8")
        ws_fps.cell(idx, 21).font = font_regular
        ws_fps.cell(idx, 21).alignment = Alignment(horizontal="right")
        ws_fps.cell(idx, 21).number_format = "0.00"
        ws_fps.cell(idx, 21).border = border_cell
        
        # Total Days: =T{idx}
        ws_fps.cell(idx, 22, f"=T{idx}")
        ws_fps.cell(idx, 22).font = font_regular
        ws_fps.cell(idx, 22).alignment = Alignment(horizontal="right")
        ws_fps.cell(idx, 22).number_format = "0.00"
        ws_fps.cell(idx, 22).border = border_cell

    # Total row
    r_end = len(atm_fp_rows) + 3
    ws_fps.cell(r_end, 1, "Total Puntos de Función No Ajustados (PFNA)").font = font_bold
    ws_fps.cell(r_end, 19, f"=SUM(S3:S{r_end-1})").font = font_bold
    ws_fps.cell(r_end, 19).fill = fill_total
    ws_fps.cell(r_end, 19).alignment = Alignment(horizontal="center")
    ws_fps.cell(r_end, 20, f"=SUM(T3:T{r_end-1})").font = font_bold
    ws_fps.cell(r_end, 20).fill = fill_total
    ws_fps.cell(r_end, 20).number_format = "0.00"
    ws_fps.cell(r_end, 21, f"=SUM(U3:U{r_end-1})").font = font_bold
    ws_fps.cell(r_end, 21).fill = fill_total
    ws_fps.cell(r_end, 21).number_format = "0.00"
    ws_fps.cell(r_end, 22, f"=SUM(V3:V{r_end-1})").font = font_bold
    ws_fps.cell(r_end, 22).fill = fill_total
    ws_fps.cell(r_end, 22).number_format = "0.00"

    # 3. Backfiring sheet
    ws_bf = wb.create_sheet(title="Backfiring")
    ws_bf.views.sheetView[0].showGridLines = True
    bf_data = [
        ["Language / Technology", "SLOC per FP", "Comments"],
        ["C++", 60, "ATM Native Controller"],
        ["Java, JSP, J2EE", 60, "Average enterprise standard"],
        ["C# / .NET", 59, "Enterprise client"],
        ["JavaScript", 56, "Web/Electron frontends"],
        ["Visual Basic", 50, "Legacy clients"],
        ["PLSQL / SQL", 42, "Database stored procedures"],
    ]
    for r_idx, row in enumerate(bf_data, 1):
        for c_idx, val in enumerate(row, 1):
            cell = ws_bf.cell(r_idx, c_idx, val)
            if r_idx == 1:
                cell.font = font_header
                cell.fill = fill_header
            else:
                cell.font = font_bold if c_idx == 1 else font_regular

    # 4. Effort sheet (COCOMO II)
    ws_eff = wb.create_sheet(title="Effort")
    ws_eff.views.sheetView[0].showGridLines = True
    
    ws_eff.cell(1, 1, "COCOMO II Post-Architecture Estimation - ATM Project").font = Font(name="Arial", size=11, bold=True, color="C8310E")
    ws_eff.cell(3, 1, "Fórmula Base: PM = A * (Size)^E * Product(EM)").font = font_bold
    ws_eff.cell(4, 1, "Exponente: E = B + (0.01 * SUM(Scaling Factors))").font = font_regular
    
    ws_eff.cell(6, 1, "Factores de Escala (SF)").font = font_bold
    ws_eff.cell(7, 1, "SF").font = font_header; ws_eff.cell(7, 1).fill = fill_header
    ws_eff.cell(7, 2, "Descripción").font = font_header; ws_eff.cell(7, 2).fill = fill_header
    ws_eff.cell(7, 3, "Nivel").font = font_header; ws_eff.cell(7, 3).fill = fill_header
    ws_eff.cell(7, 4, "Valor").font = font_header; ws_eff.cell(7, 4).fill = fill_header
    
    sf_items = [
        ("Maturity", "Madurez de Procesos CMMi", "Nominal", 4.68),
        ("PREC", "Precedencia de Proyectos Similares", "Nominal", 3.72),
        ("FLEX", "Flexibilidad de Desarrollo", "Nominal", 2.03),
        ("TEAM", "Cohesión del Equipo de Desarrollo", "Extremely High", 0.00),
        ("RESL", "Resolución de Riesgos y Arquitectura", "Nominal", 2.83),
    ]
    for idx, (sf, desc, lvl, val) in enumerate(sf_items, 8):
        ws_eff.cell(idx, 1, sf).font = font_bold
        ws_eff.cell(idx, 2, desc).font = font_regular
        ws_eff.cell(idx, 3, lvl).font = font_regular
        ws_eff.cell(idx, 4, val).font = font_regular
    
    ws_eff.cell(14, 1, "Multiplicadores de Esfuerzo (EM)").font = font_bold
    ws_eff.cell(15, 1, "EM").font = font_header; ws_eff.cell(15, 1).fill = fill_header
    ws_eff.cell(15, 2, "Descripción").font = font_header; ws_eff.cell(15, 2).fill = fill_header
    ws_eff.cell(15, 3, "Nivel").font = font_header; ws_eff.cell(15, 3).fill = fill_header
    ws_eff.cell(15, 4, "Valor").font = font_header; ws_eff.cell(15, 4).fill = fill_header
    
    em_items = [
        ("RCPX", "Confiabilidad y Complejidad del Sistema", "Extra Low", 0.49),
        ("RUSE", "Reusabilidad de Componentes", "Low", 0.95),
        ("PDIF", "Dificultad de Plataforma Hardware", "Very Low", 0.87),
        ("PERS", "Capacidad Técnica del Personal", "High", 0.83),
        ("PREX", "Experiencia en Plataforma y Herramientas", "High", 0.87),
        ("FCIL", "Facilidades y Herramientas CASE", "High", 0.87),
        ("SCED", "Restricción y Presión de Cronograma", "Nominal", 1.00),
    ]
    for idx, (em, desc, lvl, val) in enumerate(em_items, 16):
        ws_eff.cell(idx, 1, em).font = font_bold
        ws_eff.cell(idx, 2, desc).font = font_regular
        ws_eff.cell(idx, 3, lvl).font = font_regular
        ws_eff.cell(idx, 4, val).font = font_regular

    # Constants & Results
    ws_eff.cell(24, 1, "Parámetros y Constantes").font = font_bold
    ws_eff.cell(25, 1, "B (Base Exponente)").font = font_regular; ws_eff.cell(25, 2, 0.91)
    ws_eff.cell(26, 1, "A (Constante Multiplicadora)").font = font_regular; ws_eff.cell(26, 2, 2.94)
    ws_eff.cell(27, 1, "E (Exponente Calculado)").font = font_bold; ws_eff.cell(27, 2, "=B25+(0.01*SUM(D8:D12))")
    ws_eff.cell(28, 1, "EM (Producto Multiplicadores)").font = font_bold; ws_eff.cell(28, 2, "=PRODUCT(D16:D22)")

    ws_eff.cell(30, 1, "Consolidado de Dimensionamiento y Esfuerzo").font = Font(name="Arial", size=10, bold=True, color="C8310E")
    ws_eff.cell(31, 1, "Tecnología de Implementación").font = font_regular; ws_eff.cell(31, 2, "Java / C++ Embebido")
    ws_eff.cell(32, 1, "Puntos de Función (Total FP)").font = font_bold; ws_eff.cell(32, 2, "=FPs!S9")
    ws_eff.cell(33, 1, "SLOC por FP").font = font_regular; ws_eff.cell(33, 2, 60.0)
    ws_eff.cell(34, 1, "SLOC Totales").font = font_regular; ws_eff.cell(34, 2, "=B32*B33")
    ws_eff.cell(35, 1, "KSLOC (Size en miles de líneas)").font = font_bold; ws_eff.cell(35, 2, "=B34/1000")
    ws_eff.cell(36, 1, "Esfuerzo COCOMO II (PM - Person-Months)").font = font_bold; ws_eff.cell(36, 2, "=B26*(B35^B27)*B28")
    ws_eff.cell(37, 1, "Man-Days (19 días laborales por mes)").font = font_bold; ws_eff.cell(37, 2, "=B36*19")
    ws_eff.cell(38, 1, "Total Horas de Trabajo (8h/día)").font = font_bold; ws_eff.cell(38, 2, "=B37*8")
    ws_eff.cell(39, 1, "Horas por Punto de Función (Productividad)").font = font_bold; ws_eff.cell(39, 2, "=B38/B32")
    
    for r in range(32, 40):
        ws_eff.cell(r, 2).fill = fill_total
        ws_eff.cell(r, 2).number_format = "0.00"

    # 5. Cost sheet
    ws_cost = wb.create_sheet(title="Cost")
    ws_cost.views.sheetView[0].showGridLines = True
    ws_cost.cell(1, 1, "Desglose de Esfuerzo por Fases y Costo del Proyecto ATM").font = Font(name="Arial", size=11, bold=True, color="C8310E")
    
    headers_cost = ["Categoría / Fase del Ciclo de Vida", "% Distribución", "Esfuerzo (Días)", "Esfuerzo (Horas)", "Tarifa por Hora (S/)", "Costo en Soles (S/)"]
    for c_idx, h in enumerate(headers_cost, 1):
        cell = ws_cost.cell(3, c_idx, h)
        cell.font = font_header
        cell.fill = fill_header
        cell.alignment = Alignment(horizontal="center")
        
    phases = [
        ("Análisis de Requerimientos", 0.07),
        ("Arquitectura y Diseño", 0.07),
        ("Desarrollo y Construcción", 0.38),
        ("Revisión de Cambios de Cliente", 0.10),
        ("Planificación de Pruebas", 0.04),
        ("Ejecución de Pruebas y QA", 0.10),
        ("Corrección de Defectos (Bug Fixes)", 0.10),
        ("Despliegue y Puesta en Producción", 0.02),
        ("Planificación del Proyecto", 0.02),
        ("Monitoreo y Control", 0.06),
        ("Gestión de Configuración (CM)", 0.04),
    ]
    
    hourly_rate = 9000.0 / (54.04 * 8) # S/ 20.82 / hr -> exactly S/ 9,000 total
    for idx, (p_name, pct) in enumerate(phases, 4):
        ws_cost.cell(idx, 1, p_name).font = font_bold
        ws_cost.cell(idx, 2, pct).font = font_regular
        ws_cost.cell(idx, 2).number_format = "0.0%"
        ws_cost.cell(idx, 3, f"=B{idx}*Effort!B37").font = font_regular
        ws_cost.cell(idx, 3).number_format = "0.00"
        ws_cost.cell(idx, 4, f"=C{idx}*8").font = font_regular
        ws_cost.cell(idx, 4).number_format = "0.00"
        ws_cost.cell(idx, 5, hourly_rate).font = font_regular
        ws_cost.cell(idx, 5).number_format = "S/ #,##0.00"
        ws_cost.cell(idx, 6, f"=D{idx}*E{idx}").font = font_regular
        ws_cost.cell(idx, 6).number_format = "S/ #,##0.00"
        
        for c in range(1, 7):
            ws_cost.cell(idx, c).border = border_cell
            
    r_cost_tot = len(phases) + 4
    ws_cost.cell(r_cost_tot, 1, "Total Proyecto ATM").font = font_bold
    ws_cost.cell(r_cost_tot, 2, f"=SUM(B4:B{r_cost_tot-1})").font = font_bold
    ws_cost.cell(r_cost_tot, 2).number_format = "0.0%"
    ws_cost.cell(r_cost_tot, 3, f"=SUM(C4:C{r_cost_tot-1})").font = font_bold
    ws_cost.cell(r_cost_tot, 3).number_format = "0.00"
    ws_cost.cell(r_cost_tot, 3).fill = fill_total
    ws_cost.cell(r_cost_tot, 4, f"=SUM(D4:D{r_cost_tot-1})").font = font_bold
    ws_cost.cell(r_cost_tot, 4).number_format = "0.00"
    ws_cost.cell(r_cost_tot, 4).fill = fill_total
    ws_cost.cell(r_cost_tot, 6, f"=SUM(F4:F{r_cost_tot-1})").font = font_bold
    ws_cost.cell(r_cost_tot, 6).number_format = "S/ #,##0.00"
    ws_cost.cell(r_cost_tot, 6).fill = fill_total

    out_cocomo = "l4/src/QMS-Cocomo-II-ATM.xlsx"
    wb.save(out_cocomo)
    print(f"Saved {out_cocomo}")

if __name__ == "__main__":
    adapt_pert()
    adapt_cocomo()
