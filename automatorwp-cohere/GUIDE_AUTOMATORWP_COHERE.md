# AutomatorWP Cohere – Guía de Pruebas y Acciones Avanzadas

Este documento sirve como guía para la auditoría, pruebas y ampliación de funcionalidades del plugin AutomatorWP Cohere.

## Índice
- [Introducción](#introducción)
- [Checklist de pruebas actuales](#checklist-de-pruebas-actuales)
- [Propuestas de nuevas acciones](#propuestas-de-nuevas-acciones)
- [Checklist para nuevas acciones](#checklist-para-nuevas-acciones)

---

## Introducción

Esta guía recopila los pasos de prueba realizados sobre el plugin AutomatorWP Cohere, así como propuestas de nuevas acciones basadas en las capacidades más recientes de la API de Cohere. El objetivo es facilitar la validación, ampliación y mantenimiento del plugin.

---

## Checklist de pruebas actuales

- [x] Send prompt to Cohere
- [x] Send message in a Cohere conversation
- [x] Clean conversation
- [x] Summarize text with Cohere
- [x] Classify text into categories with Cohere
- [x] Rerank documents by relevance with Cohere
- [x] Generate text embeddings with Cohere
- [x] Generate text with Cohere
- [x] Translate text with Cohere

---

## Propuestas de nuevas acciones

1. **Detección de Sentimientos (Sentiment Analysis)**
   - Analiza un texto y devuelve si el sentimiento es positivo, negativo o neutro.
2. **Detección de Temas (Topic Detection)**
   - Identifica el tema principal de un texto.
3. **Extracción de Respuestas (Answer Extraction / Q&A)**
   - Dado un contexto y una pregunta, extrae la respuesta más relevante.
4. **Detección de Entidades (Named Entity Recognition, NER)**
   - Extrae entidades como nombres, lugares, organizaciones, fechas, etc.
5. **Detección de Lenguaje (Language Detection)**
   - Detecta automáticamente el idioma de un texto.
6. **Generación de Resúmenes Avanzados (Advanced Summarization)**
   - Resúmenes personalizados, controlando longitud o estilo.
7. **Moderación de Contenido (Content Moderation)**
   - Detecta contenido ofensivo, spam o no permitido.

---

## Checklist para nuevas acciones

- [ ] Sentiment Analysis: ¿Se puede implementar con la API actual de Cohere? ¿Devuelve resultados útiles?
- [ ] Topic Detection: ¿La API de Cohere soporta esta funcionalidad? ¿Qué precisión tiene?
- [ ] Answer Extraction: ¿Permite Cohere extraer respuestas de un contexto dado?
- [ ] Named Entity Recognition: ¿Cohere ofrece NER? ¿Qué tipos de entidades reconoce?
- [ ] Language Detection: ¿La API detecta correctamente el idioma de textos variados?
- [ ] Advanced Summarization: ¿Se pueden generar resúmenes personalizados?
- [ ] Content Moderation: ¿Cohere puede identificar contenido no permitido o sensible?