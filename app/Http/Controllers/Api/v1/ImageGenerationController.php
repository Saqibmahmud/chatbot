<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\GeneratePromptRequest;
use App\Http\Resources\ImageGenerationResource;
use App\Services\GeminiService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ImageGenerationController extends Controller
{
    private $gemini_service;
    
    public function __construct(GeminiService $gemini_service)
    {
        $this->gemini_service = $gemini_service;
    }
    
     
    
    /**
     * All images prompts
     */
    public function index(Request $request)
    {
      $user= request()->user();
  if($request->has('search') && isset($request->search)){
     
    $imageGenerations=$user->imageGenerations()->where('generated_prompt','LIKE', "%$request->search%"  )->paginate();
  }
else{
    $imageGenerations= $user->imageGenerations()->latest()->paginate();
}
    
        return ImageGenerationResource::collection($imageGenerations);
    }
   /**
     * Generate Prompt from Image
     *
     * Upload an image and get a comprehensive prompt that can be used to regenerate a similar image using an AI chatbot.
     *
     * @bodyParam image file required The image file to analyze and generate a prompt for.
     *
     * @responseFile status=201 storage/responses/image-generations/store.json
     *
     * @authenticated
     * @author:saqib
     */
    public function store(GeneratePromptRequest $request)
    {
        try {
            $user = $request->user();
            $image = $request->file('image');

            // Generate prompt first (before storing to save storage on failures)
            $generatedPrompt = $this->gemini_service->generatePromptFromImage($image);

            // Prepare file name
            $originalName = $image->getClientOriginalName();
            $sanitizedName = preg_replace('/[^a-zA-Z0-9_\.-]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
            $extension = $image->getClientOriginalExtension();
            $finalFileName = $sanitizedName . '_' . time() . '.' . $extension;

            // Store image
            $imagePath = $image->storeAs('uploads/images', $finalFileName, 'public');

            // Get file metadata
            $fileSize = $image->getSize();
            $mimeType = $image->getMimeType();

            // Create database record
            $generation = \App\Models\ImageGereration::create([
                'user_id'           => $user ? $user->id : null,
                'generated_prompt'  => $generatedPrompt,
                'image_path'        => $imagePath,
                'original_filename' => $originalName,
                'file_size'         => $fileSize,
                'mime_type'         => $mimeType,
            ]);

return new ImageGenerationResource($generation);

            // return response()->json([
            //     'message' => 'Image processed successfully',
            //     'data' => $generation
            // ], 201);
            
        } catch (\Exception $e) {
            Log::error('Image generation error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'user_id' => $request->user() ? $request->user()->id : null
            ]);
            
            return response()->json([
                'message' => 'An error occurred while processing the image',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}