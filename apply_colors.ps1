# Script: apply_complementary_colors.ps1
# Applies complementary accent colors to projects while keeping primary colors.

$ErrorActionPreference = 'Stop'

function Update-Files {
    param(
        [string]$RootPath,
        [hashtable]$Replacements,
        [string[]]$Extensions
    )

    $extSet = @{}
    foreach ($e in $Extensions) { $extSet[$e.ToLower()] = $true }

    Get-ChildItem -Path $RootPath -Recurse -File | ForEach-Object {
        $file = $_
        if ($file.Name.StartsWith('.')) { return }
        if (-not $extSet.ContainsKey($file.Extension.ToLower())) { return }

        $content = $null
        try {
            $content = [System.IO.File]::ReadAllText($file.FullName)
        } catch {
            return
        }

        $newContent = $content
        $matched = $false
        foreach ($key in $Replacements.Keys) {
            if ($newContent.Contains($key)) {
                $newContent = $newContent.Replace($key, $Replacements[$key])
                $matched = $true
            }
        }

        if ($matched -and $newContent -ne $content) {
            $utf8NoBom = New-Object System.Text.UTF8Encoding($false)
            [System.IO.File]::WriteAllText($file.FullName, $newContent, $utf8NoBom)
            Write-Host "UPDATED: $($file.FullName)"
        }
    }
}

# ============================================================
# 1. Project-Elpis — keep Deep Blue #3F5195 + Teal #4FA08A
#    Replace Yellow accent #E4CF55 -> Coral #E76F51 (complement of teal)
# ============================================================
Update-Files -RootPath 'c:/xampp/htdocs/work_folder/Project-Elpis' -Replacements @{
    '#E4CF55' = '#E76F51'
    '#d4bf45' = '#c2603f'
} -Extensions @('.php', '.css')

# ============================================================
# 2. realRealestate — keep Blue #1565C0
#    Change --accent from blue #1E88E5 -> Amber #FF9800 (complement)
#    Also warm the primary hover/glow rgba to amber.
# ============================================================
Update-Files -RootPath 'c:/xampp/htdocs/work_folder/realRealestate' -Replacements @{
    '--accent: #1E88E5' = '--accent: #FF9800'
    '--accent:#1E88E5'  = '--accent:#FF9800'
    'rgba(21, 101, 192, 0.1)' = 'rgba(255, 152, 0, 0.15)'
} -Extensions @('.php', '.css')

# ============================================================
# 3. todoList — keep Blue #2563eb, add Amber #F59E0B accent
# ============================================================
Update-Files -RootPath 'c:/xampp/htdocs/work_folder/todoList' -Replacements @{
    '--primary: #2563eb;' = '--primary: #2563eb; --accent: #F59E0B;'
    'rgba(37, 99, 235, 0.18)' = 'rgba(245, 158, 11, 0.18)'
} -Extensions @('.css')

# ============================================================
# 4. WiFiSales — keep Blue #2563eb, add Amber #F59E0B accent
# ============================================================
Update-Files -RootPath 'c:/xampp/htdocs/work_folder/WiFiSales' -Replacements @{
    '--primary: #2563eb;' = '--primary: #2563eb; --accent: #F59E0B;'
    'rgba(37, 99, 235, 0.15)' = 'rgba(245, 158, 11, 0.18)'
    'rgba(37, 99, 235, 0.18)' = 'rgba(245, 158, 11, 0.18)'
} -Extensions @('.css')

# ============================================================
# 5. portfolio — keep Blue #2839d2, add Warm Amber #F59E0B
# ============================================================
Update-Files -RootPath 'c:/xampp/htdocs/work_folder/portfolio' -Replacements @{
    'color: #2839d2;' = 'color: #2563eb;'
} -Extensions @('.css')

# ============================================================
# 6. writing_dev — keep Purple #7c3aed + Green, add Gold #FBBF24
# ============================================================
Update-Files -RootPath 'c:/xampp/htdocs/work_folder/writing_dev' -Replacements @{
    'rgba(124, 58, 237, 0.16)' = 'rgba(251, 191, 36, 0.16)'
    'rgba(124, 58, 237, 0.5)'  = 'rgba(251, 191, 36, 0.6)'
    'rgba(124, 58, 237, 0.6)'  = 'rgba(251, 191, 36, 0.6)'
} -Extensions @('.css')

Write-Host "`n=== Safe global color replacements complete ==="

