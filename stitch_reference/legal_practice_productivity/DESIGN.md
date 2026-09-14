---
name: Legal Practice Productivity
colors:
  surface: '#faf9f6'
  surface-dim: '#dbdad7'
  surface-bright: '#faf9f6'
  surface-container-lowest: '#ffffff'
  surface-container-low: '#f4f3f1'
  surface-container: '#efeeeb'
  surface-container-high: '#e9e8e5'
  surface-container-highest: '#e3e2e0'
  on-surface: '#1a1c1a'
  on-surface-variant: '#424843'
  inverse-surface: '#2f312f'
  inverse-on-surface: '#f1f1ee'
  outline: '#727973'
  outline-variant: '#c1c8c1'
  surface-tint: '#436651'
  primary: '#022616'
  on-primary: '#ffffff'
  primary-container: '#1a3c2a'
  on-primary-container: '#82a78f'
  inverse-primary: '#a9cfb6'
  secondary: '#755b00'
  on-secondary: '#ffffff'
  secondary-container: '#fed977'
  on-secondary-container: '#785d00'
  tertiary: '#1c221f'
  on-tertiary: '#ffffff'
  tertiary-container: '#313733'
  on-tertiary-container: '#9aa09b'
  error: '#ba1a1a'
  on-error: '#ffffff'
  error-container: '#ffdad6'
  on-error-container: '#93000a'
  primary-fixed: '#c5ecd2'
  primary-fixed-dim: '#a9cfb6'
  on-primary-fixed: '#002112'
  on-primary-fixed-variant: '#2c4e3a'
  secondary-fixed: '#ffe08f'
  secondary-fixed-dim: '#e6c364'
  on-secondary-fixed: '#241a00'
  on-secondary-fixed-variant: '#584400'
  tertiary-fixed: '#dee4df'
  tertiary-fixed-dim: '#c2c8c3'
  on-tertiary-fixed: '#171d1a'
  on-tertiary-fixed-variant: '#424844'
  background: '#faf9f6'
  on-background: '#1a1c1a'
  surface-variant: '#e3e2e0'
typography:
  display:
    fontFamily: Inter
    fontSize: 2.25rem
    fontWeight: '600'
    lineHeight: 2.75rem
    letterSpacing: -0.025em
  headline-lg:
    fontFamily: Inter
    fontSize: 1.75rem
    fontWeight: '600'
    lineHeight: 2.25rem
    letterSpacing: -0.02em
  headline-lg-mobile:
    fontFamily: Inter
    fontSize: 1.375rem
    fontWeight: '600'
    lineHeight: 1.75rem
    letterSpacing: -0.015em
  headline-md:
    fontFamily: Inter
    fontSize: 1.25rem
    fontWeight: '600'
    lineHeight: 1.75rem
    letterSpacing: -0.015em
  headline-sm:
    fontFamily: Inter
    fontSize: 1rem
    fontWeight: '600'
    lineHeight: 1.5rem
    letterSpacing: -0.01em
  body-lg:
    fontFamily: Inter
    fontSize: 1rem
    fontWeight: '400'
    lineHeight: 1.625rem
    letterSpacing: -0.005em
  body-md:
    fontFamily: Inter
    fontSize: 0.875rem
    fontWeight: '400'
    lineHeight: 1.375rem
    letterSpacing: 0em
  body-sm:
    fontFamily: Inter
    fontSize: 0.75rem
    fontWeight: '400'
    lineHeight: 1.125rem
    letterSpacing: 0em
  label-md:
    fontFamily: Inter
    fontSize: 0.8125rem
    fontWeight: '500'
    lineHeight: 1.125rem
    letterSpacing: 0.01em
  label-sm:
    fontFamily: Inter
    fontSize: 0.6875rem
    fontWeight: '600'
    lineHeight: 1rem
    letterSpacing: 0.03em
  code-md:
    fontFamily: JetBrains Mono
    fontSize: 0.8125rem
    fontWeight: '400'
    lineHeight: 1.25rem
    letterSpacing: -0.01em
  code-sm:
    fontFamily: JetBrains Mono
    fontSize: 0.6875rem
    fontWeight: '500'
    lineHeight: 1rem
    letterSpacing: 0em
rounded:
  sm: 0.125rem
  DEFAULT: 0.25rem
  md: 0.375rem
  lg: 0.5rem
  xl: 0.75rem
  full: 9999px
spacing:
  gutter: 1rem
  gutter-lg: 1.5rem
  margin: 1rem
  margin-md: 1.5rem
  margin-lg: 2rem
  space-3xs: 0.125rem
  space-2xs: 0.25rem
  space-xs: 0.5rem
  space-sm: 0.75rem
  space-md: 1rem
  space-lg: 1.5rem
  space-xl: 2rem
  space-2xl: 3rem
  space-3xl: 4rem
---

## Brand & Style

This design system targets mid-market to enterprise legal practices, litigation boutiques, and modern general counsels who demand the velocity and keyboard-first precision of Linear paired with the grounded, archival trust of traditional legal practice. The atmosphere is quiet, confident, and meticulously organized—rejecting generic corporate blue in favor of warm, editorial materiality.

The design movement is **Modern Archival Functionalism**: a synthesis of high-density productivity software and classic editorial restraint. Surfaces rely on warm parchment-derived neutrals instead of sterile digital whites, paired with authoritative deep forest greens and sharp, low-noise line work. The emotional response is immediate clarity, judicial composure, and rigorous reliability under time-critical deadlines.

## Colors

The palette establishes an immediate sense of institutional permanence through a warm, organic scale rather than cool tech-grays.

### Surface and Canvas Architecture
- `background-base` (`#FBFAF7`) serves as the foundational canvas for the entire viewport, providing an eye-resting tone for intensive reading sessions.
- `surface-canvas` (`#FFFFFF`) is reserved exclusively for elevated content tiles, document panes, and modal sheets to introduce pure optical contrast against the warm foundation.
- `shell-sidebar` (`#111714`) isolates navigation, global search, and command menus in a deep, near-black evergreen chrome, creating an unmistakable boundary between workspace frame and matter content.

### Action and Accent Hierarchy
- `primary` (`#1A3C2A`) commands primary interactive weight—finalizing filings, time-entry submissions, and document generation.
- `accent-gold` (`#C9A84C`) is strictly functional: reserved for billable status tags, trust balance thresholds, retainer indicators, and privilege assertions. It must never be overused for generic action triggers.
- Status indications (Emerald `#10B981`, Azure `#3B82F6`, Amber `#F59E0B`, Rose `#EF4444`) are calibrated with matched pastel tint backgrounds (10% opacity) for dense tabular scanning without visual screaming.

## Typography

The typographic hierarchy prioritizes rapid scannability, exact tabular alignment, and zero cognitive fatigue across dense litigation binders and financial dockets.

- **Interface & Headings:** Set in `Inter` with tuned tabular numeric figures (`tnum`) enabled system-wide. Tighter tracking at display and headline scales preserves compact structure while maintaining neutral authority.
- **Matter Identifiers & Telemetry:** All docket numbers (e.g., `HO-2026-0042`), UTBMS billing task codes, ledger entries, and audit trail timestamps strictly utilize `JetBrains Mono`. This enforces clear visual separation between legal commentary and factual record parameters.
- **Labels & Micro-Copy:** Small uppercase labels leverage expanded letter-spacing (`0.03em`) and medium/semibold weights to ensure instant recognition inside dense metadata headers and property sidebars.

## Layout & Spacing

The layout model enforces a strict 4px base rhythm designed to accommodate multi-panel split views (matter navigator, active dossier, and context inspector) within a single desktop window.

### Panel Structure
- **Desktop (>= 1280px):** Fixed-width shell sidebar (240px collapsable to 56px icon rail), primary content pane (fluid, minimum 640px), and an optional collapsible context/preview drawer (360px to 440px). Gutters inside working surfaces maintain a strict `1rem` (16px) or `1.5rem` (24px) pace.
- **Tablet (768px - 1279px):** Sidebar converts to an off-canvas drawer or top toolbar; inspector drawer converts to a modal sheet; grid scales to 8 columns with `1rem` gutters.
- **Mobile (< 768px):** Single-column stacked workflow. Shell controls fold into a bottom sheet navigation system with `margin` pinned to `1rem`.

### Density Model
All functional gaps utilize the `space-*` scale. Inline property metadata pairs use `space-2xs` (4px) to `space-xs` (8px); form input vertical stacks use `space-sm` (12px); structured case card interiors standardise on `space-md` (16px).

## Elevation & Depth

Visual separation relies predominantly on hairline borders and delicate, warm ambient shadows rather than stark elevation jumps.

- **Level 0 (Base Canvas):** Background tone `#FBFAF7`. No shadow. Elements are framed cleanly using `1px solid #E5E0D6`.
- **Level 1 (Work Cards & Panels):** White `#FFFFFF` panels resting on `#FBFAF7`. Defined by a compound treatment: a crisp structural border (`1px solid #E5E0D6`) combined with an ultra-soft resting shadow: `0 1px 3px rgba(17, 23, 20, 0.04), 0 1px 2px rgba(17, 23, 20, 0.02)`.
- **Level 2 (Hover States & Flyouts):** Active list selections, dropdown menus, and quick-search panels: `0 4px 12px rgba(17, 23, 20, 0.06), 0 2px 4px rgba(17, 23, 20, 0.03)`, retaining the `1px solid #D5CEBF` perimeter.
- **Level 3 (Modals & Command Palettes):** Centered dialogs, billing time-capture popovers, and spotlight search: `0 12px 32px rgba(17, 23, 20, 0.12), 0 4px 8px rgba(17, 23, 20, 0.04)` over an ink-wash backdrop blur (`rgba(17, 23, 20, 0.45)` with `backdrop-filter: blur(4px)`).

## Shapes

The interface embraces a tailored, architectural edge profile consistent with roundedness level `1` (0.25rem / 4px base):

- **Inputs, Checkboxes, Buttons:** 6px (`0.375rem`), yielding a precise, reliable mechanical touchpoint that matches dense data inputs.
- **Cards, Document Panels, Sheet Containers:** 8px (`0.5rem` / `rounded-lg`), producing neat, crisp container boundaries that maintain linear alignment across multiple nested panes.
- **Badges, Status Chips, Billable Tags:** Pill-radius (9999px), providing high contrast in silhouette against rectangular legal text containers and docket rows.

## Components

### Buttons
- **Primary:** Background `#1A3C2A`, label white `#FFFFFF`, height 32px (compact) or 38px (regular), border radius 6px, subtle inset top highlight (`box-shadow: inset 0 1px 0 rgba(255,255,255,0.12)`). Hover: `#2D5A3D`. Active: `#142F21`.
- **Secondary:** Surface `#FFFFFF`, border `1px solid #E5E0D6`, text `#1C1C1C`. Hover: `#F3EFE6` border `#D5CEBF`.
- **Ghost/Tertiary:** Transparent background, text `#4A4A4A`. Hover: `#EDE8DD` text `#1C1C1C`.

### Input Fields & Controls
- **Text Inputs:** Height 34px, background `#FFFFFF`, border `1px solid #E5E0D6`, radius 6px, text `#1C1C1C`, typography `body-md`. Focus: border `#1A3C2A`, ring `2px solid rgba(26, 60, 42, 0.15)`. Placeholder: `#717171`.
- **Checkboxes & Radios:** 16px square/circle, border `1.5px solid #D5CEBF`, radius 4px (checkbox). Checked state: background `#1A3C2A`, border `#1A3C2A`, check icon crisp white.

### Chips & Badges
- **Status Badges:** Height 20px, pill-shaped (radius 9999px), horizontal padding 8px, typography `label-sm`.
  - Active/Success: Background `#ECFDF5`, text `#065F46`, border `1px solid #A7F3D0`.
  - Billable/Retainer: Background `#FDF9EF`, text `#8A6D1C`, border `1px solid #E4D5A4`.
  - Overdue/Urgent: Background `#FEF2F2`, text `#991B1B`, border `1px solid #FECACA`.

### Cards & Panels
- **Matter Dossier Card:** Pure white background, border `1px solid #E5E0D6`, radius 8px, padding 16px. Header features matter title in `headline-sm`, client name in `body-sm text-secondary`, and matter ID badge in `code-sm` right-aligned.

### Data Tables & Docket Lists
- Row height 36px (dense) or 44px (default). Top/bottom divider `1px solid #EDE8DD`. Hover row background `#F3EFE6`. Active selected row: `#F3EFE6` with an absolute 2px left border accent in `#1A3C2A`.