<?php

namespace App\Http\Controllers\API\v1\Auth;

use App\Helpers\ResponseError;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\AuthService\AuthByEmail;
use App\Services\AuthService\AuthByMobilePhone;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Twilio\Exceptions\ConfigurationException;

class RegisterController extends Controller
{
    use ApiResponse;

    /**
     * @throws ConfigurationException
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        
        $requestData = json_encode($_REQUEST, JSON_PRETTY_PRINT);
        $folder = 'D:/sollywood-Admin-local/admin_backend/logs/';
        $filename = 'register_data.txt';
        $filepath = $folder . $filename;
        file_put_contents($filepath, $requestData);

        $collection = $request->validated();       
       // dd($collection);     
        $user = User::where([
            ['phone', $request->input('phone')],
            ['phone', '!=', 'NULL']
        ])->orWhere([
            ['email', $request->input('email')],
            ['email', '!=', 'NULL']
        ])->first();
       // dd($user);
        if ($user){
            return $this->errorResponse(ResponseError::ERROR_106, trans('errors.' . ResponseError::ERROR_106, [], $this->language), Response::HTTP_BAD_REQUEST);
        }

        if (isset($collection['phone'])){
            return (new AuthByMobilePhone())->authentication($collection);
        } elseif (isset($collection['email'])) {
            return (new AuthByEmail())->authentication($collection);
        }
        return $this->errorResponse(ResponseError::ERROR_400, 'errors.'.ResponseError::ERROR_400, Response::HTTP_BAD_REQUEST);
    }

   
}
