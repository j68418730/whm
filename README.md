# Planet Hosts Control Panel

## Project Overview

Planet Hosts is a modern hosting automation and server management platform designed to provide a complete alternative to traditional hosting control panels. The platform combines website hosting management, reseller management, billing automation, DNS management, email services, database administration, radio streaming management, and customer support into a single unified interface.

The project is built with a modular architecture where every major feature operates as an independent module. This allows administrators to enable, disable, update, or extend functionality without affecting the core platform.

The primary goal is to provide a lightweight, modern, API-driven hosting ecosystem that eliminates the need for multiple disconnected applications while maintaining a clean user experience for clients, resellers, and administrators.

---

## Supported Environment

### Operating System

Currently tested and supported on:

* Debian 12
* Debian 11

Future support planned for:

* Ubuntu Server
* AlmaLinux
* Rocky Linux

The platform should be considered Debian-only until additional operating systems have been fully tested.

---

## Core Modules

### Authentication

* User Registration
* Login / Logout
* Password Recovery
* Two-Factor Authentication
* Session Management
* API Authentication
* Role-Based Permissions

### Client Area

* Dashboard
* Service Management
* Resource Usage
* Billing Overview
* Support Tickets
* Notifications
* Account Settings

### Admin Area

* System Dashboard
* User Management
* Reseller Management
* Service Management
* Monitoring
* Security Controls
* Audit Logs
* System Configuration

### Reseller Area

* Client Management
* Package Management
* Resource Allocation
* Branding Controls
* Usage Statistics
* Revenue Reporting

---

## Hosting Management

### Website Hosting

* Create Hosting Accounts
* Suspend Accounts
* Unsuspend Accounts
* Terminate Accounts
* Change Account Passwords
* Package Assignment
* Resource Limits

### Domain Management

* Domain Registration
* Domain Transfers
* Domain Renewals
* DNS Management
* Nameserver Management
* DNSSEC Support

### DNS Automation

When a domain is added:

* Create DNS Zone
* Generate SOA Record
* Generate NS Records
* Generate A Records
* Generate MX Records
* Generate SPF Records
* Generate DMARC Records
* Generate DKIM Records
* Reload DNS Services

### Email Services

* Email Accounts
* Forwarders
* Autoresponders
* Spam Protection
* DKIM
* SPF
* DMARC
* Webmail Access

### Database Management

* MySQL
* MariaDB
* PostgreSQL
* Database Users
* Database Permissions
* phpMyAdmin Integration

### SSL Management

* Let's Encrypt
* AutoSSL
* Manual Certificates
* Certificate Renewal
* SSL Monitoring

---

## File Management

* File Manager
* Upload Files
* Download Files
* Archive Extraction
* Compression
* Backups
* Restore Operations

---

## Billing Platform

### Billing Features

* Products
* Services
* Orders
* Invoices
* Transactions
* Taxes
* Coupons
* Credits

### Automation

* Service Provisioning
* Automatic Suspension
* Automatic Unsuspension
* Automatic Termination
* Renewal Processing
* Invoice Generation

---

## Support System

* Ticket Management
* Departments
* Internal Notes
* Attachments
* Knowledgebase
* Announcements
* Server Status Page

---

## Monitoring

### Server Monitoring

* CPU Usage
* Memory Usage
* Disk Usage
* Network Usage
* Service Status
* Uptime Monitoring

### Service Monitoring

* Web Server Monitoring
* Database Monitoring
* Email Monitoring
* DNS Monitoring
* Streaming Monitoring

---

## Radio Streaming Platform

Planet Hosts includes a complete radio streaming management platform designed as an alternative to Centova Cast.

### Supported Streaming Servers

* Icecast 2
* Icecast KH
* SHOUTcast v1 Not Support Yet do to os
* SHOUTcast v2 Not Support Yet do to os

### Station Management

* Create Radio Station
* Suspend Station
* Unsuspend Station
* Delete Station
* Package Assignment
* Resource Limits

### Streaming Controls

* Start Stream
* Stop Stream
* Restart Stream
* Mount Point Management
* Source Password Management

### AutoDJ

* Media Library
* Playlist Management
* Scheduled Playlists
* Rotation Rules
* Jingles
* Scheduled Events

### DJ Management

* Create DJ Accounts
* Edit DJ Accounts
* Delete DJ Accounts
* DJ Permissions
* DJ Scheduling

### Listener Statistics

* Current Listeners
* Peak Listeners
* Listener History
* Geographic Statistics
* Device Statistics
* Bandwidth Usage

### HTML5 Player System

* Embedded Players
* Floating Players
* Popup Players
* Mobile Players
* Now Playing Widgets
* Recently Played Widgets
* Listener Counters

### Streaming APIs

* Now Playing API
* Listener Statistics API
* Station Information API
* Player API

### Public Station Pages

* Station Profile
* Now Playing
* Recently Played
* Schedule
* DJ Profiles
* Social Links
* Embedded Players

---

## Plugin Architecture

Every feature can be developed as a module.

Example modules:

* Hosting
* Domains
* DNS
* Email
* Billing
* Support
* Monitoring
* Radio
* CMS
* API

Each module contains:

* Controllers
* Views
* Database Migrations
* API Endpoints
* Permissions
* Assets
* Widgets

---

## API Platform

### REST API

* Authentication
* Service Management
* Billing Management
* DNS Management
* Radio Management
* Monitoring

### Webhooks

* Account Created
* Account Suspended
* Invoice Paid
* Domain Added
* Station Created
* Service Terminated

---

## Design System

Theme:

* Dark Mode First
* Space Inspired Interface
* Planet Hosts Branding
* Glassmorphism Components
* Neon Blue Accents
* Responsive Layout

Supported Devices:

* Desktop
* Tablet
* Mobile

---

## Project Goal

Planet Hosts aims to provide a complete hosting ecosystem that combines:

* Website Hosting
* DNS Management
* Email Hosting
* Database Management
* Billing Automation
* Customer Support
* Monitoring
* Radio Streaming
* Reseller Services

into a single platform while remaining lightweight, modular, and optimized for Debian-based servers.
