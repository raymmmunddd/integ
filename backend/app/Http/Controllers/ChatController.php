<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'model' => 'required|string',
            'messages' => 'required|array',
            'temperature' => 'nullable|numeric|min:0|max:2',
            'max_tokens' => 'nullable|integer|min:1|max:4000',
        ]);

        $apiKey = env('OPENROUTER_API_KEY');
        
        if (!$apiKey) {
            Log::error('OpenRouter API key not configured');
            return response()->json([
                'error' => 'Configuration error',
                'message' => 'API key not configured. Please contact support.'
            ], 500);
        }

        // Log the request for debugging
        Log::info('Chat request received', [
            'model' => $validated['model'],
            'message_count' => count($validated['messages'])
        ]);

        try {
            $response = Http::timeout(60) // Increased to 60 seconds
                ->retry(2, 100) // Retry twice with 100ms delay
                ->withHeaders([
                    'Authorization' => 'Bearer ' . $apiKey,
                    'Content-Type' => 'application/json',
                    'HTTP-Referer' => config('app.url'),
                    'X-Title' => 'Therapy Assistant',
                ])
                ->post('https://openrouter.ai/api/v1/chat/completions', [
                    'model' => $validated['model'],
                    'messages' => $validated['messages'],
                    'temperature' => $validated['temperature'] ?? 0.7,
                    'max_tokens' => $validated['max_tokens'] ?? 300,
                ]);

            // Log response status
            Log::info('OpenRouter response', [
                'status' => $response->status(),
                'successful' => $response->successful()
            ]);

            if ($response->failed()) {
                $errorData = $response->json();
                Log::error('OpenRouter API failed', [
                    'status' => $response->status(),
                    'error' => $errorData
                ]);
                
                // Check for specific error types
                if (isset($errorData['error'])) {
                    $errorMessage = $errorData['error']['message'] ?? 'Unknown API error';
                    
                    // Handle rate limiting
                    if ($response->status() === 429) {
                        return response()->json([
                            'error' => 'Rate limit exceeded',
                            'message' => 'Too many requests. Please wait a moment and try again.'
                        ], 429);
                    }
                    
                    // Handle authentication errors
                    if ($response->status() === 401) {
                        return response()->json([
                            'error' => 'Authentication failed',
                            'message' => 'Invalid API key. Please contact support.'
                        ], 500);
                    }
                    
                    return response()->json([
                        'error' => 'API request failed',
                        'message' => $errorMessage
                    ], $response->status());
                }
                
                return response()->json([
                    'error' => 'API request failed',
                    'message' => 'Unable to get response from AI service'
                ], $response->status());
            }

            $data = $response->json();
            
            // Validate response structure
            if (!isset($data['choices']) || empty($data['choices']) || 
                !isset($data['choices'][0]['message']['content'])) {
                Log::error('Invalid API response structure', ['response' => $data]);
                
                return response()->json([
                    'error' => 'Invalid response',
                    'message' => 'Received unexpected response format from AI service'
                ], 500);
            }

            Log::info('Chat response successful');
            return response()->json($data);
            
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('Connection error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Connection timeout',
                'message' => 'Unable to connect to AI service. Please check your internet connection and try again.'
            ], 504);
            
        } catch (\Illuminate\Http\Client\RequestException $e) {
            Log::error('Request error', [
                'error' => $e->getMessage(),
                'response' => $e->response ? $e->response->body() : null
            ]);
            
            return response()->json([
                'error' => 'Request failed',
                'message' => 'Failed to communicate with AI service'
            ], 500);
            
        } catch (\Exception $e) {
            Log::error('Unexpected error in chat', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => 'Server error',
                'message' => 'An unexpected error occurred. Please try again.'
            ], 500);
        }
    }
}