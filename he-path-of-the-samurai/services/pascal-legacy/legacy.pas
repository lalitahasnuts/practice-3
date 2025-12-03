program LegacyCSV;

{$mode objfpc}{$H+}

uses
  SysUtils, DateUtils, Process;

function GetEnvDef(const name, def: string): string;
var v: string;
begin
  v := GetEnvironmentVariable(name);
  if v = '' then Exit(def) else Exit(v);
end;

function GetEnvIntDef(const name: string; def: Integer): Integer;
begin
  Result := StrToIntDef(GetEnvDef(name, IntToStr(def)), def);
end;

function RandFloat(minV, maxV: Double): Double;
begin
  Result := minV + Random * (maxV - minV);
end;

function XmlEscape(const s: string): string;
var
  i: Integer;
begin
  Result := '';
  for i := 1 to Length(s) do
  begin
    case s[i] of
      '&': Result += '&amp;';
      '<': Result += '&lt;';
      '>': Result += '&gt;';
      '"': Result += '&quot;';
      '''': Result += '&apos;';
    else
      Result += s[i];
    end;
  end;
end;

procedure PrintAsTable(const recordedAt, voltage, temp, sourceFile: string);
var
  h1, h2, h3, h4: string;
  w1, w2, w3, w4: Integer;
  sep: string;

  function Pad(const s: string; width: Integer): string;
  begin
    Result := s + StringOfChar(' ', width - Length(s));
  end;

begin
  h1 := 'recorded_at';
  h2 := 'voltage';
  h3 := 'temp';
  h4 := 'source_file';

  w1 := Length(h1);
  w2 := Length(h2);
  w3 := Length(h3);
  w4 := Length(h4);

  if Length(recordedAt) > w1 then w1 := Length(recordedAt);
  if Length(voltage) > w2 then w2 := Length(voltage);
  if Length(temp) > w3 then w3 := Length(temp);
  if Length(sourceFile) > w4 then w4 := Length(sourceFile);

  sep := '+' + StringOfChar('-', w1 + 2) +
         '+' + StringOfChar('-', w2 + 2) +
         '+' + StringOfChar('-', w3 + 2) +
         '+' + StringOfChar('-', w4 + 2) + '+';

  WriteLn(sep);
  WriteLn('| ', Pad(h1, w1), ' | ', Pad(h2, w2), ' | ',
              Pad(h3, w3), ' | ', Pad(h4, w4), ' |');
  WriteLn(sep);
  WriteLn('| ', Pad(recordedAt, w1), ' | ', Pad(voltage, w2), ' | ',
              Pad(temp, w3), ' | ', Pad(sourceFile, w4), ' |');
  WriteLn(sep);
end;

procedure GenerateXlsx(const outDir, ts: string;
  const recordedAt, voltage, temp, sourceFile: string);
var
  tmpDir, relsDir, xlDir, xlRelsDir, wsDir: string;
  xlsxPath: string;
  f: TextFile;
  cmd: string;
begin
  // Готовим временную структуру директорий для минимального XLSX
  tmpDir := IncludeTrailingPathDelimiter('/tmp') + 'legacy_xlsx_' + ts;
  if not DirectoryExists(tmpDir) then
    if not CreateDir(tmpDir) then Exit;

  relsDir := IncludeTrailingPathDelimiter(tmpDir) + '_rels';
  if not DirectoryExists(relsDir) then
    if not CreateDir(relsDir) then Exit;

  xlDir := IncludeTrailingPathDelimiter(tmpDir) + 'xl';
  if not DirectoryExists(xlDir) then
    if not CreateDir(xlDir) then Exit;

  xlRelsDir := IncludeTrailingPathDelimiter(xlDir) + '_rels';
  if not DirectoryExists(xlRelsDir) then
    if not CreateDir(xlRelsDir) then Exit;

  wsDir := IncludeTrailingPathDelimiter(xlDir) + 'worksheets';
  if not DirectoryExists(wsDir) then
    if not CreateDir(wsDir) then Exit;

  // [Content_Types].xml
  AssignFile(f, IncludeTrailingPathDelimiter(tmpDir) + '[Content_Types].xml');
  Rewrite(f);
  WriteLn(f, '<?xml version="1.0" encoding="UTF-8"?>');
  WriteLn(f, '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">');
  WriteLn(f, '  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>');
  WriteLn(f, '  <Default Extension="xml" ContentType="application/xml"/>');
  WriteLn(f, '  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>');
  WriteLn(f, '  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>');
  WriteLn(f, '</Types>');
  CloseFile(f);

  // _rels/.rels
  AssignFile(f, IncludeTrailingPathDelimiter(relsDir) + '.rels');
  Rewrite(f);
  WriteLn(f, '<?xml version="1.0" encoding="UTF-8"?>');
  WriteLn(f, '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">');
  WriteLn(f, '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>');
  WriteLn(f, '</Relationships>');
  CloseFile(f);

  // xl/workbook.xml
  AssignFile(f, IncludeTrailingPathDelimiter(xlDir) + 'workbook.xml');
  Rewrite(f);
  WriteLn(f, '<?xml version="1.0" encoding="UTF-8"?>');
  WriteLn(f, '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"');
  WriteLn(f, '          xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">');
  WriteLn(f, '  <sheets>');
  WriteLn(f, '    <sheet name="telemetry" sheetId="1" r:id="rId1"/>');
  WriteLn(f, '  </sheets>');
  WriteLn(f, '</workbook>');
  CloseFile(f);

  // xl/_rels/workbook.xml.rels
  AssignFile(f, IncludeTrailingPathDelimiter(xlRelsDir) + 'workbook.xml.rels');
  Rewrite(f);
  WriteLn(f, '<?xml version="1.0" encoding="UTF-8"?>');
  WriteLn(f, '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">');
  WriteLn(f, '  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>');
  WriteLn(f, '</Relationships>');
  CloseFile(f);

  // xl/worksheets/sheet1.xml с одной строкой заголовка и одной строкой данных
  AssignFile(f, IncludeTrailingPathDelimiter(wsDir) + 'sheet1.xml');
  Rewrite(f);
  WriteLn(f, '<?xml version="1.0" encoding="UTF-8"?>');
  WriteLn(f, '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">');
  WriteLn(f, '  <sheetData>');
  // Header row
  WriteLn(f, '    <row r="1">');
  WriteLn(f, '      <c r="A1" t="str"><v>recorded_at</v></c>');
  WriteLn(f, '      <c r="B1" t="str"><v>voltage</v></c>');
  WriteLn(f, '      <c r="C1" t="str"><v>temp</v></c>');
  WriteLn(f, '      <c r="D1" t="str"><v>source_file</v></c>');
  WriteLn(f, '    </row>');
  // Data row
  WriteLn(f, '    <row r="2">');
  WriteLn(f, '      <c r="A2" t="str"><v>' + XmlEscape(recordedAt) + '</v></c>');
  WriteLn(f, '      <c r="B2"><v>' + XmlEscape(voltage) + '</v></c>');
  WriteLn(f, '      <c r="C2"><v>' + XmlEscape(temp) + '</v></c>');
  WriteLn(f, '      <c r="D2" t="str"><v>' + XmlEscape(sourceFile) + '</v></c>');
  WriteLn(f, '    </row>');
  WriteLn(f, '  </sheetData>');
  WriteLn(f, '</worksheet>');
  CloseFile(f);

  // Путь к итоговому XLSX
  xlsxPath := IncludeTrailingPathDelimiter(outDir) + 'telemetry_' + ts + '.xlsx';

  // Упаковка во внешний zip → полноценный .xlsx
  cmd := 'sh -lc ''cd ' + tmpDir + ' && zip -qr ' + xlsxPath + ' .''';
  fpSystem(cmd);
end;

procedure GenerateAndCopy();
var
  outDir, fn, fullpath, pghost, pgport, pguser, pgpass, pgdb, copyCmd: string;
  f: TextFile;
  ts: string;
  recordedAtStr, voltageStr, tempStr: string;
begin
  outDir := GetEnvDef('CSV_OUT_DIR', '/data/csv');
  ts := FormatDateTime('yyyymmdd_hhnnss', Now);
  fn := 'telemetry_' + ts + '.csv';
  fullpath := IncludeTrailingPathDelimiter(outDir) + fn;

  // write CSV
  AssignFile(f, fullpath);
  Rewrite(f);
  Writeln(f, 'recorded_at,voltage,temp,source_file');
  // recorded_at: TIMESTAMP (без таймзоны в CSV, парсится как TIMESTAMPTZ в БД)
  // voltage/temp: числовой формат без локальных разделителей
  // source_file: текстовое поле
  recordedAtStr := FormatDateTime('yyyy-mm-dd hh:nn:ss', Now);
  voltageStr := FormatFloat('0.00', RandFloat(3.2, 12.6));
  tempStr := FormatFloat('0.00', RandFloat(-50.0, 80.0));

  Writeln(f, recordedAtStr + ',' + voltageStr + ',' + tempStr + ',' + fn);
  CloseFile(f);

  // Визуализация CSV как таблицы в stdout
  PrintAsTable(recordedAtStr, voltageStr, tempStr, fn);

  // Генерация XLSX с теми же значениями (дата/время, числа, текст)
  GenerateXlsx(outDir, ts, recordedAtStr, voltageStr, tempStr, fn);

  // COPY into Postgres
  pghost := GetEnvDef('PGHOST', 'db');
  pgport := GetEnvDef('PGPORT', '5432');
  pguser := GetEnvDef('PGUSER', 'monouser');
  pgpass := GetEnvDef('PGPASSWORD', 'monopass');
  pgdb   := GetEnvDef('PGDATABASE', 'monolith');

  // Use psql with COPY FROM PROGRAM for simplicity
  // Here we call psql reading from file
  copyCmd := 'psql "host=' + pghost + ' port=' + pgport + ' user=' + pguser + ' dbname=' + pgdb + '" ' +
             '-c "\copy telemetry_legacy(recorded_at, voltage, temp, source_file) FROM ''' + fullpath + ''' WITH (FORMAT csv, HEADER true)"';
  // Mask password via env
  SetEnvironmentVariable('PGPASSWORD', pgpass);
  // Execute
  fpSystem(copyCmd);
end;

var
  period: Integer;
  rateWindowSec, rateMax: Integer;
  rateWindowStart: TDateTime;
  rateWindowCount: Integer;
  nowDt: TDateTime;
begin
  Randomize;
  period := StrToIntDef(GetEnvDef('GEN_PERIOD_SEC', '300'), 300);
  // Rate-limit: не более rateMax запусков за rateWindowSec секунд
  rateWindowSec := GetEnvIntDef('LEGACY_RATE_WINDOW_SEC', 60);
  rateMax := GetEnvIntDef('LEGACY_RATE_MAX', 10);
  rateWindowStart := Now;
  rateWindowCount := 0;
  while True do
  begin
    nowDt := Now;
    if (rateWindowSec > 0) and (SecondsBetween(nowDt, rateWindowStart) >= rateWindowSec) then
    begin
      rateWindowStart := nowDt;
      rateWindowCount := 0;
    end;

    if (rateMax > 0) and (rateWindowCount >= rateMax) then
    begin
      WriteLn('Legacy rate-limit: window full, skipping generation this cycle');
    end
    else
    begin
      try
        GenerateAndCopy();
        Inc(rateWindowCount);
      except
        on E: Exception do
          WriteLn('Legacy error: ', E.Message);
      end;
    end;
    Sleep(period * 1000);
  end;
end.
