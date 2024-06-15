<?php

namespace App\Http\Controllers\API\v1\Rest;
use App\Models\User;
use App\Models\UserCart;
use App\Models\Shop;
use App\Models\Product;
use App\Models\CartDetail;
use App\Models\Category;
use App\Models\GiftSetting;
use App\Helpers\ResponseError;
use App\Http\Requests\FilterParamsRequest;
use App\Http\Resources\ProductResource;
use App\Http\Resources\ShopProductResource;
use App\Http\Resources\ShopProductSearchResource;
use App\Models\Point;
use App\Repositories\CategoryRepository\CategoryRepository;
use App\Repositories\Interfaces\ProductRepoInterface;
use App\Repositories\ProductRepository\RestProductRepository;
use App\Repositories\ShopRepository\ShopRepository;
use App\Services\OrderService\OrderService;
use App\Services\ProductService\ProductReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends RestBaseController
{
    public function __construct(protected RestProductRepository $restProductRepository,protected ProductRepoInterface $productRepository)
    {
        parent::__construct();
        $this->middleware('sanctum.check')->only('addProductReview');
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function paginate(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $products = $this->restProductRepository->productsPaginate(
            $request->input('perPage', 15),
            true,
            $request->merge(['rest' => true])->all()
        );
        return ShopProductResource::collection($products);
    }

    /**
     * Display a listing of the resource.
     *
     * @param FilterParamsRequest $request
     * @return AnonymousResourceCollection
     */
    public function giftCartProducts(FilterParamsRequest $request): AnonymousResourceCollection
    {
        $products = $this->restProductRepository->productsPaginate(
            $request->input('perPage', 15),
            true,
            $request->merge(['rest' => true])->all()
        );

        return ShopProductResource::collection($products);
    }

    /**
     * Display the specified resource.
     *
     * @param string $uuid
     * @return JsonResponse
     */
    public function show(string $uuid): JsonResponse
    {
        $product = $this->restProductRepository->productByUUID($uuid);

        if ($product) {
            return $this->successResponse(__('errors.'.ResponseError::NO_ERROR), ShopProductResource::make($product));
        }

        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    /**
     * Display the specified resource.
     *
     * @param string $slug
     * @return JsonResponse
     */
    public function showBySlug(string $slug): JsonResponse
    {
        $product = $this->restProductRepository->productBySlug($slug);

        if ($product) {
            return $this->successResponse(__('errors.'.ResponseError::NO_ERROR), ShopProductResource::make($product));
        }

        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }
	
    public function productsByShopUuid(FilterParamsRequest $request, string $uuid): JsonResponse|AnonymousResourceCollection
    {
        $shop = (new ShopRepository())->shopDetails($uuid);
        if ($shop) {
            $products = $this->productRepository->productsPaginate($request->perPage ?? 15, true, ['shop_id' => $shop->id, 'rest' => true]);
            return ProductResource::collection($products);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    public function productsByBrand(FilterParamsRequest $request, int $id): AnonymousResourceCollection
    {
        $products = $this->productRepository->productsPaginate($request->perPage ?? 15, true, ['brand_id' => $id, 'rest' => true]);
        return ProductResource::collection($products);
    }

    public function productsByCategoryUuid(FilterParamsRequest $request, string $uuid): JsonResponse|AnonymousResourceCollection
    {
        $category = (new CategoryRepository())->categoryByUuid($uuid);
        if ($category) {
            $products = $this->productRepository->productsPaginate($request->perPage ?? 15, true, ['category_id' => $category->id, 'rest' => true]);
            return ProductResource::collection($products);
        }
        return $this->errorResponse(
            ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
            Response::HTTP_NOT_FOUND
        );

    }

    /**
     * Search Model by tag name.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function productsSearch(Request $request): AnonymousResourceCollection
    {
        $products = $this->restProductRepository->productsPaginateSearch($request->input('perPage', 15), true, $request->all());
        return ShopProductSearchResource::collection($products);
    }

    public function mostSoldProducts(FilterParamsRequest $request): JsonResponse|AnonymousResourceCollection
    {
        $products = $this->restProductRepository->productsMostSold($request->perPage,$request->all());
        if ($products->count() > 0)
            return ShopProductResource::collection($products);
        else
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
                Response::HTTP_NOT_FOUND
            );
    }

    /**
     * Search Model by tag name.
     *
     * @param string $uuid
     * @param Request $request
     * @return JsonResponse
     */
    public function addProductReview(string $uuid, Request $request): JsonResponse
    {
        $result = (new ProductReviewService)->addReview($uuid, $request);

        if (data_get($result, 'status')) {
            return $this->successResponse(ResponseError::NO_ERROR, []);
        }

        return $this->errorResponse(
            data_get($result, 'code', ResponseError::ERROR_404),
            trans('errors.' . data_get($result, 'code', ResponseError::ERROR_404), [], $this->language),
            Response::HTTP_NOT_FOUND
        );
    }

    public function discountProducts(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $products = $this->restProductRepository->productsDiscount($request->perPage ?? 15, $request->all());
        if ($products->count() > 0)
            return ShopProductResource::collection($products);
        else
            return $this->errorResponse(
                ResponseError::ERROR_404, trans('errors.' . ResponseError::ERROR_404, [], $this->language),
                Response::HTTP_NOT_FOUND
            );

    }

    public function productsCalculate(Request $request): JsonResponse
    {
        $result = (new OrderService())->orderProductsCalculate($request->all());
        return $this->successResponse(__('web.products_calculated'), $result);
    }

    /**
     * Get Products by IDs.
     *
     * @param Request $request
     * @return AnonymousResourceCollection
     */
    public function productsByIDs(Request $request): AnonymousResourceCollection
    {
        $products = $this->restProductRepository->productsByIDs($request->products);
        return ShopProductResource::collection($products);
    }

    public function checkCashback(Request $request): JsonResponse
    {
        $point = Point::getActualPoint($request->amount ?? 0);
        return $this->successResponse(__('web.cashback'), ['price' => $point]);
    }

    public function buyWithProduct(int $id): AnonymousResourceCollection
    {
        $products = $this->restProductRepository->buyWithProduct($id);
        return ShopProductResource::collection($products);
    }
    
    public function getCashback(Request $request): JsonResponse
    {
        $user = User::where('uuid', $request->user_token)->first();
        $user_id = User::where('uuid', $user->uuid)->get('id')->toArray();
        $referral_to = User::where('referral',$user->my_referral)->get('firstname')->toArray();
        $referral_userId = User::where('referral',$user->my_referral)->get('id')->toArray();
      $compare_userId = UserCart::whereIn('user_id', $referral_userId)->pluck('id')->toArray();
      sort($compare_userId);
      $compare_uuid =$compare_userId;
      $compare_names = UserCart::whereIn('id', $compare_uuid)->pluck('name')->toArray();
      $matched_names = [];
        $shop_id = Shop::where('user_id',$referral_userId)->get('id')->toArray();
      $cart_det = CartDetail::whereIn('user_cart_id', $compare_uuid)->get(['price', 'quantity'])->toArray();
        $cart_details = CartDetail::whereIn('user_cart_id', $compare_uuid)->get(['price', 'quantity','shop_product_id','user_cart_id']);
        $matched_cart_details = [];
        foreach ($cart_details as $detail) {
            if (in_array($detail->user_cart_id, $compare_uuid)) {
                $matched_cart_details[] = $detail;
            }
        }
        $price_quantity_product = [];
        $shop_product_ids = CartDetail::whereIn('user_cart_id', $compare_uuid)->pluck('shop_product_id')->toArray();
       $product_id_counts = array_count_values($shop_product_ids);
       $product_ids = array_keys($product_id_counts);
       $category_ids = Product::whereIn('id', $product_ids)->pluck('category_id', 'id')->toArray();
       $category_ids_for_duplicates = [];
       $category_ids_for_non_duplicates = [];
       
       foreach ($product_id_counts as $product_id => $count) {
           if ($count > 1) {
               $category_ids_for_duplicate = array_fill(0, $count, $category_ids[$product_id]);
               $category_ids_for_duplicates = array_merge($category_ids_for_duplicates, $category_ids_for_duplicate);
           } else {
               $category_ids_for_non_duplicates[] = $category_ids[$product_id];
           }
       }
       $category_ids = array_merge($category_ids_for_duplicates, $category_ids_for_non_duplicates);
        $category_ids_unique = array_unique($category_ids);
        $referral_percentages = Category::whereIn('id', $category_ids_unique)->pluck('referralPercentage', 'id')->toArray();
        $referral_percentage_for_duplicates = [];
        $referral_percentage_for_non_duplicates = [];
        foreach ($category_ids as $category_id) {
            $referral_percentage = $referral_percentages[$category_id];
            if (in_array($category_id, $category_ids_unique)) {
                $referral_percentage_for_duplicates[] = $referral_percentage;
            } else {
                $referral_percentage_for_non_duplicates[] = $referral_percentage;
            }
        }
        $all_referral_percentages = array_merge($referral_percentage_for_duplicates, $referral_percentage_for_non_duplicates);
       $total_price_percentage_sum = 0;
       $max_total_purchase = 0;
        $max_purchase_name = '';
        foreach ($matched_cart_details as $cart_detail) {
           
            $product = [
                'name' => $compare_names[array_search($cart_detail->user_cart_id, $compare_uuid)],
                'total_purchase' => $cart_detail->price * $cart_detail->quantity,
            ];
       
            $product['total_price_percentage'] = ($product['total_purchase'] * $all_referral_percentages[array_search($cart_detail->user_cart_id, $compare_uuid)]) / 100;
            $total_price_percentage_sum += $product['total_price_percentage'];
            
            if ($product['total_purchase'] > $max_total_purchase) {
                $max_total_purchase = $product['total_purchase'];
                $max_purchase_name = $product['name'];
            }
            $price_quantity_product[] = $product;
           
        }
        if (!empty($price_quantity_product)) {
            $shop_product_ids = CartDetail::whereIn('user_cart_id', $compare_uuid)->pluck('shop_product_id')->toArray();
           $products =  Product::whereIn('id', $shop_product_ids)->get([ 'id','category_id'])->toArray();
            $products_cId = Product::whereIn('id', $shop_product_ids)->get( 'category_id')->toArray();
            $products_id = Product::whereIn('id', $shop_product_ids)->get('id')->toArray();
            $referral_percentages = []; 
       
            $giftAmount = GiftSetting::value('gift_amount');
            $giftSettings = trim($giftAmount, '"');
            $giftSettings = intval($giftSettings);

            $cartId = UserCart::where('user_id', $referral_userId)->get('cart_id')->toArray();
            foreach ($products as $product) {
                $category_ids = Product::whereIn('id', $shop_product_ids)->get('category_id')->toArray();
             $referral_percentage = Category::whereIn('id', $category_ids)->pluck('referralPercentage')->toArray();
            }  
            $response = [
                'user' => [
                    'uuid' => $user->uuid,
                ],
           
                'purchases' => $price_quantity_product,
                'max_purchase_name' => $max_purchase_name, 
                'total_caseback' => $total_price_percentage_sum,
				'gift_amount'=> $giftSettings
            ];
            
        
                return $this->successResponse("done", $response);
        
        }
        
       //$referral_from = User::where('my_referral',$user->referral)->get('firstname')->toArray();
       // return $this->successResponse("ssss",$data);
        return $this->successResponse("fetch referral user succesfully",["referral_to"=>array_column($referral_to,"firstname"),"referral_userId"=>array_column($referral_userId,"uuid")]);
    
    }
	
	 public function CashbackNotification()
    {
        $users = User::all();
      
        $a = [];
        foreach($users as $user){
            $referral_userId = User::where('referral',$user->my_referral)->get('id')->toArray();
         $firebase_token = $user->firebase_token;
		 if(empty($firebase_token)) continue;
		  
	//	dd($firebase_token);



            
            $compare_userId = UserCart::whereIn('user_id', $referral_userId)->whereDate('created_at', today())->pluck('id')->toArray();
            sort($compare_userId);
            $compare_uuid = $compare_userId;
            $cart_details = CartDetail::whereIn('user_cart_id', $compare_uuid)->get(['price', 'quantity','shop_product_id','user_cart_id']);

            $matched_cart_details = [];
            foreach ($cart_details as $detail) {
                if (in_array($detail->user_cart_id, $compare_uuid)) {
                    $matched_cart_details[] = $detail;
                }
            }

            $shop_product_ids = CartDetail::whereIn('user_cart_id', $compare_uuid)->pluck('shop_product_id')->toArray();
            $product_id_counts = array_count_values($shop_product_ids);
            $product_ids = array_keys($product_id_counts);
            $category_ids = Product::whereIn('id', $product_ids)->pluck('category_id', 'id')->toArray();
            $category_ids_for_duplicates = [];
            $category_ids_for_non_duplicates = [];
            
           foreach ($product_id_counts as $product_id => $count) {
                if ($count > 1) {
                    $category_ids_for_duplicate = array_fill(0, $count, $category_ids[$product_id]);
                    $category_ids_for_duplicates = array_merge($category_ids_for_duplicates, $category_ids_for_duplicate);
                } else {
                    $category_ids_for_non_duplicates[] = $category_ids[$product_id];
                }
            }

            $category_ids = array_merge($category_ids_for_duplicates, $category_ids_for_non_duplicates);
            $category_ids_unique = array_unique($category_ids);
            $referral_percentages = Category::whereIn('id', $category_ids_unique)->pluck('referralPercentage', 'id')->toArray();
            $referral_percentage_for_duplicates = [];
            $referral_percentage_for_non_duplicates = [];
            foreach ($category_ids as $category_id) {
                $referral_percentage = $referral_percentages[$category_id];
                if (in_array($category_id, $category_ids_unique)) {
                    $referral_percentage_for_duplicates[] = $referral_percentage;
                } else {
                    $referral_percentage_for_non_duplicates[] = $referral_percentage;
                }
            }
            $all_referral_percentages = array_merge($referral_percentage_for_duplicates, $referral_percentage_for_non_duplicates);
            $total_price_percentage_sum = 0;
            foreach ($matched_cart_details as $cart_detail) {
                $product = [
                    'total_purchase' => $cart_detail->price * $cart_detail->quantity,
                ];
        
                $product['total_price_percentage'] = ($product['total_purchase'] * $all_referral_percentages[array_search($cart_detail->user_cart_id, $compare_uuid)]) / 100;
                $total_price_percentage_sum += $product['total_price_percentage'];
            }
            $response = [];

            if ($total_price_percentage_sum > 0) {
                // Add user data to the response array
                $response[] = [
                    'uuid' => $user->uuid,
                    'total_caseback' => $total_price_percentage_sum,
                    'token' => $firebase_token[0]
                ];
            
                // Define URL and JSON data
                $url = 'https://fcm.googleapis.com/fcm/send';
                $data = array(
                    'to' => $firebase_token[0],
                    'notification' => array(
                        'body' => "You lost ".$total_price_percentage_sum." RS Cashback Today",
                        'title' => "Caseback"
                    )
                );
              
                // Initialize cURL session
                $curl = curl_init();
                
                // Set cURL options
                curl_setopt_array($curl, array(
                    CURLOPT_URL => $url,
                    CURLOPT_CAINFO => '/path/to/cacert.pem',
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => '',
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 0,
                    CURLOPT_FOLLOWLOCATION => true,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => 'POST',
                    CURLOPT_POSTFIELDS => json_encode($data),
                    CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json',
                        'Authorization: key=AAAAjWgdQIc:APA91bHhDQ6wMcL1qHseBqfJ3LlIu9p81cniD3FBqo8N9dtmTKVlmGtPXu1dro0V8uMt-NeS1vnRfg-9eK19vOHS-ZuSbUBqPwm6cqyTZpovqJNKBsvflqh7Ym73k9P9AqYTZUbOoxEB'
                    ),
                ));
            
                // Execute cURL request
                $response = curl_exec($curl);
                
                // Handle errors
                if ($response === false) {
                    $error = curl_error($curl);
                    // Handle the error appropriately, such as logging it
                    echo "cURL Error: " . $error;
                }
            
                // Close cURL session
                curl_close($curl);
				$a[]=$response;
            }
            
            
           
        }
        return $this->successResponse("done", $a);
    }
}
