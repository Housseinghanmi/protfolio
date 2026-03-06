/**
 * Netlify Serverless Function — Visitor Tracker
 * Replaces: track-visitor.php, config.php, get-analytics.php
 * 
 * Environment variables required (set in Netlify dashboard):
 *   SUPABASE_URL       → your project URL
 *   SUPABASE_ANON_KEY  → your anon/public key
 */

const RATE_LIMIT_MAP = {}; // in-memory, resets on cold start
const BOT_PATTERNS = /bot|crawler|spider|curl|wget|python|java(?!script)/i;

// ─── Helpers ─────────────────────────────────────────────────────────────────

function getClientIP(headers) {
  return (
    headers['x-forwarded-for']?.split(',')[0].trim() ||
    headers['x-real-ip'] ||
    'unknown'
  );
}

function isBot(userAgent = '') {
  return BOT_PATTERNS.test(userAgent);
}

function checkRateLimit(ip, maxPerMinute = 10) {
  const now = Date.now();
  const windowMs = 60 * 1000;

  if (!RATE_LIMIT_MAP[ip]) RATE_LIMIT_MAP[ip] = [];

  // Remove entries older than 1 minute
  RATE_LIMIT_MAP[ip] = RATE_LIMIT_MAP[ip].filter(t => now - t < windowMs);
  RATE_LIMIT_MAP[ip].push(now);

  return RATE_LIMIT_MAP[ip].length <= maxPerMinute;
}

function getBrowserName(userAgent = '') {
  if (/firefox/i.test(userAgent))  return 'Firefox';
  if (/edg/i.test(userAgent))      return 'Edge';
  if (/chrome/i.test(userAgent))   return 'Chrome';
  if (/safari/i.test(userAgent))   return 'Safari';
  if (/opera|opr/i.test(userAgent)) return 'Opera';
  return 'Unknown';
}

// ─── Supabase insert ──────────────────────────────────────────────────────────

async function saveToSupabase(data) {
  const url  = process.env.SUPABASE_URL;
  const key  = process.env.SUPABASE_ANON_KEY;

  if (!url || !key) {
    console.error('Missing SUPABASE_URL or SUPABASE_ANON_KEY env variables');
    return false;
  }

  const response = await fetch(`${url}/rest/v1/visitors`, {
    method: 'POST',
    headers: {
      'Content-Type':  'application/json',
      'apikey':        key,
      'Authorization': `Bearer ${key}`,
      'Prefer':        'return=minimal'
    },
    body: JSON.stringify(data)
  });

  if (!response.ok) {
    const err = await response.text();
    console.error('Supabase insert failed:', err);
    return false;
  }

  return true;
}

// ─── Main handler ─────────────────────────────────────────────────────────────

export async function handler(event) {
  const headers = {
    'Access-Control-Allow-Origin':  '*',
    'Access-Control-Allow-Methods': 'POST, OPTIONS',
    'Access-Control-Allow-Headers': 'Content-Type',
    'Content-Type':                 'application/json'
  };

  // Handle CORS preflight
  if (event.httpMethod === 'OPTIONS') {
    return { statusCode: 204, headers, body: '' };
  }

  if (event.httpMethod !== 'POST') {
    return { statusCode: 405, headers, body: JSON.stringify({ error: 'Method not allowed' }) };
  }

  const clientIP  = getClientIP(event.headers);
  const userAgent = event.headers['user-agent'] || '';

  // Block bots
  if (isBot(userAgent)) {
    return { statusCode: 403, headers, body: JSON.stringify({ error: 'Forbidden' }) };
  }

  // Rate limiting
  if (!checkRateLimit(clientIP, 10)) {
    return { statusCode: 429, headers, body: JSON.stringify({ error: 'Rate limited' }) };
  }

  // Parse body
  let visitorData;
  try {
    visitorData = JSON.parse(event.body);
  } catch {
    return { statusCode: 400, headers, body: JSON.stringify({ error: 'Invalid JSON' }) };
  }

  // Build record
  const record = {
    timestamp:   visitorData.timestamp   || new Date().toISOString(),
    page:        visitorData.page        || '/',
    page_title:  visitorData.pageTitle   || '',
    referrer:    visitorData.referrer    || 'direct',
    user_agent:  getBrowserName(userAgent),
    language:    visitorData.language    || '',
    timezone:    visitorData.timezone    || '',
    session_id:  visitorData.sessionId   || '',
    ip_address:  clientIP,
    latitude:    visitorData.latitude    ?? null,
    longitude:   visitorData.longitude   ?? null,
    accuracy:    visitorData.accuracy    ?? null,
    recorded_at: new Date().toISOString()
  };

  // Save to Supabase
  await saveToSupabase(record);

  return {
    statusCode: 200,
    headers,
    body: JSON.stringify({ status: 'success' })
  };
}