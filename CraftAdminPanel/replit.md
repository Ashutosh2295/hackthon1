# Tribal Arts Heritage Platform

## Overview

This project is a web platform dedicated to preserving tribal art and culture while supporting indigenous artists. The platform serves as both a showcase for traditional arts and a marketplace connecting artists with collectors and art enthusiasts. It features a public-facing website with cultural content and an administrative panel for content management, built with a focus on cultural sensitivity and authentic representation of tribal heritage.

## User Preferences

Preferred communication style: Simple, everyday language.

## System Architecture

### Frontend Architecture
- **Static HTML/CSS Implementation**: The project uses traditional HTML with custom CSS for the frontend, emphasizing performance and accessibility
- **Cultural Design System**: Custom CSS variables define a culturally-appropriate color palette (terracotta red, golden yellow, earthy green) that reflects tribal art aesthetics
- **Responsive Design**: Mobile-first approach with flexible layouts to ensure accessibility across devices
- **Typography Strategy**: Combines serif fonts (Georgia) for readability and cultural authenticity with modern sans-serif fonts (Poppins) for UI elements

### Administrative Interface
- **Separate Admin Panel**: Dedicated admin section with its own styling and functionality for content management
- **Role-based Access**: Login system for administrative users to manage content, artists, and cultural artifacts
- **Content Management**: Interface for adding, editing, and organizing tribal art pieces, artist profiles, and cultural information

### Styling Architecture
- **CSS Custom Properties**: Extensive use of CSS variables for consistent theming and easy maintenance
- **Component-based Styling**: Modular CSS approach with reusable classes and components
- **Cultural Color Palette**: Carefully chosen colors that respect and reflect tribal art traditions
- **Accessibility Focus**: High contrast ratios and semantic markup for inclusive design

### File Organization
- **Separation of Concerns**: Clear distinction between public-facing content and administrative functionality
- **Asset Management**: Organized structure for CSS, images, and other static assets
- **Modular CSS**: Separate stylesheets for different sections (main site vs admin panel)

## External Dependencies

### Fonts and Typography
- **Google Fonts**: Integration with Georgia and Poppins font families for consistent typography across browsers
- **Font Awesome**: Icon library for UI elements and visual enhancements

### Styling Libraries
- **Font Awesome CDN**: Version 6.0.0 for scalable vector icons
- **Google Fonts API**: For web font delivery and performance optimization

### Browser Compatibility
- **Modern CSS Features**: Utilizes CSS custom properties, flexbox, and grid for modern browser support
- **Progressive Enhancement**: Fallback fonts and graceful degradation for older browsers