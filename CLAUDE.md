# Claude Instructions

## Code Style
- I am a junior developer, always add clear comments explaining what the code does
- Keep code readable and educational
- Explain why, not just what

## Git Commits
- At the end of each session, summarize what was done
- Suggest a clear commit message following this format:
  `type(scope): short description`
  Example: `feat(auth): add login validation`

## CSS Organization
When adding or editing styles in `src/assets/css/style.css`, always respect this exact section order:

1. **CSS Custom Properties / Design Tokens** — `:root` variables (typography, colors, transitions, z-index)
2. **Base / Global Reset** — `html`, `body`, element-level resets
3. **Landing Page** (`LandingPage.jsx`) — `.landing-layout`, `.landing-counter`, `.landing-logo-slot`, `.logoMain`
4. **Page Transition Overlay** (`TransitionOverlay.jsx`) — `.transitionContainer`, `.block`, `body.run-reveal`
5. **Koyko Home Page** (`HomePage.jsx` + `Koyko*` components), in order of visual appearance:
   - Navbar (`KoykoNavbar.jsx`) — `.koyko-nav*`
   - Hero (`KoykoHero.jsx`) — `.koyko-hero*`
   - Mission (`KoykoMission.jsx`) — `.koyko-mission*`
   - Features Marquee (`KoykoFeatures.jsx`) — `.koyko-features*`
   - Portfolio (`KoykoPortfolio.jsx`) — `.koyko-portfolio*`
   - Designed With Love (`KoykoDesigned.jsx`) — `.koyko-designed*`
   - Divider — `.koyko-divider`
   - Contact (`KoykoContact.jsx`) — `.koyko-contact*`
   - Footer (`KoykoFooter.jsx`) — `.koyko-footer*`
6. **New pages go here** — when a new route/page is added, insert its section block between the last page and the media queries, labeled with the component file name
7. **All Media Queries** — collected at the very bottom, ordered smallest → largest breakpoint. `prefers-reduced-motion` goes here too.

### Rules:
- **Never scatter `@media` blocks** throughout the file — all go at the bottom
- Each section must open with a comment header like:
```css
  /* =============================================================================
     SECTION NAME  (ComponentFile.jsx)
     Brief description of what this section covers.
     ============================================================================= */
```
- When adding styles for a **new component**, match where it appears visually on the page (top → bottom) and insert it in the right section
- When adding styles for a **new page/route**, add a new labeled section block before the media queries
- Only add tokens to `:root` if they are reused in 2+ places — avoid one-off variables