<?php

namespace App\Http\Controllers\api;

   /**
 * @OA\Info(
 *    title="APIs For application",
 *    version="1.0.0",
 *    description="API endpoints for the application.",
 *    @OA\Contact(
 *         email="viracodingGplus@gmail.com",
 *         name="viracoding",
 *      
 *     )
 * ),
* @OA\Server(
 *     url="http://localhost:8000/api/v1",
 *     description="development Server"
 * ),
 *  @OA\Server(
 *     url="https://shadmanup.ir/api/v1",
 *     description="Production Server"
 * ),
 * @OA\SecurityScheme(
 *       securityScheme="bearerAuth",
 *       in="header",
 *       name="bearerAuth",
 *       type="http",
 *       scheme="bearer",
 *       bearerFormat="JWT",
 *    ),
 * 
  * @OA\Schema(
 *     schema="User",
 *     title="User",
 *     description="User model schema",
 *     @OA\Property(property="avatar", type="string"),
 *     @OA\Property(property="referral", type="string"),
 *     @OA\Property(property="cellphone", type="string"),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="email_verified_at", type="string", format="date-time"),
 *     @OA\Property(property="firstname", type="string"),
 *     @OA\Property(property="national_code", type="string"),
 *     @OA\Property(property="lastname", type="string"),
 *     @OA\Property(property="wallet", type="number", format="float"),
 *     @OA\Property(property="wallet_expire", type="string", format="date-time"),
 *     @OA\Property(property="wallet_gift", type="number", format="float"),
 *     @OA\Property(property="password", type="string"),
 *     @OA\Property(property="phone_code", type="string"),
 *     @OA\Property(property="phone_code_send_time", type="string", format="date-time"),
 *     @OA\Property(property="role", type="integer"),
 *     @OA\Property(property="referrer", type="string"),
 *     @OA\Property(property="ref_level", type="integer"),
 *     @OA\Property(property="login_level", type="integer"),
 *     @OA\Property(property="login", type="string"),
 * )
 *  * @OA\Schema(
 *     schema="Category",
 *     title="Category",
 *     description="Category model schema",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="parent_id", type="integer", nullable=true),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 * )
 * * @OA\Schema(
 *     schema="PaginationLinks",
 *     @OA\Property(property="first", type="string", description="URL to the first page"),
 *     @OA\Property(property="last", type="string", description="URL to the last page"),
 *     @OA\Property(property="prev", type="string", description="URL to the previous page"),
 *     @OA\Property(property="next", type="string", description="URL to the next page"),
 * )
 * @OA\Schema(
 *     schema="PaginationMeta",
 *     @OA\Property(property="current_page", type="integer", description="Current page number"),
 *     @OA\Property(property="from", type="integer", description="Index of the first item in the current page"),
 *     @OA\Property(property="last_page", type="integer", description="Total number of pages"),
 *     @OA\Property(property="path", type="string", description="URL path for the current page"),
 *     @OA\Property(property="per_page", type="integer", description="Number of items per page"),
 *     @OA\Property(property="to", type="integer", description="Index of the last item in the current page"),
 *     @OA\Property(property="total", type="integer", description="Total number of items"),
 * )
 *  * @OA\Schema(
 *     schema="ValidationError",
 *     title="ValidationError",
 *     description="خطای اعتبارسنجی",
 *     @OA\Property(property="data", type="object"),
 *     @OA\Property(property="status", type="integer", example=404),
 *     @OA\Property(property="success", type="boolean", example=false),
 *     @OA\Property(property="message", type="string", example="دسته والد وجود ندارد!"),
 *     @OA\Property(property="errors", type="array", @OA\Items()),
 * )
 * 
 * @OA\Schema(
 *     schema="Course",
 *     title="Course",
 *     description="Represents a course.",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         format="int64",
 *         description="The unique identifier for the course."
 *     ),
 *     @OA\Property(
 *         property="title",
 *         type="string",
 *         description="The title of the course."
 *     ),
 *     @OA\Property(
 *         property="category_id",
 *         type="integer",
 *         format="int64",
 *         description="The ID of the category to which the course belongs."
 *     ),
 *     @OA\Property(
 *         property="author",
 *         type="string",
 *         description="The author of the course."
 *     ),
 *     @OA\Property(
 *         property="description",
 *         type="string",
 *         description="A brief description of the course."
 *     ),
 *     @OA\Property(
 *         property="price",
 *         type="number",
 *         description="The price of the course."
 *     ),
 *     @OA\Property(
 *         property="discount",
 *         type="number",
 *         description="The discount applied to the course."
 *     ),
 *     @OA\Property(
 *         property="final_price",
 *         type="number",
 *         description="The total price of sections course."
 *     ),
 *     @OA\Property(
 *         property="summary",
 *         type="string",
 *         description="A summary of the course content."
 *     ),
 *     @OA\Property(
 *         property="total_duration_time",
 *         type="string",
 *         description="total duration time of the course"
 *     ),
 *     @OA\Property(property="comments", type="array", @OA\Items(ref="#/components/schemas/CommentCourse")),
 *     @OA\Property(
 *         property="sessions_count",
 *         type="string",
 *         description="total sessions counts of the course"
 *     ),
 *     @OA\Property(
 *         property="comments_count",
 *         type="string",
 *         description="total comments counts of the course"
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         description="if status is bought it mean that the course is bought from user if is empty it means that the course not buy from user"
 *     ),
 *     @OA\Property(
 *         property="available_for_purchase",
 *         type="string",
 *         description="The attribute signifies whether an item or product is currently accessible for purchase. If this attribute is set to true, it means that the item is currently available for customers to buy. Conversely, if it is set to false, it indicates that the item is not currently available for purchase. "
 *     ),
 *     @OA\Property(
 *         property="image",
 *         type="string",
 *         format="binary",
 *         description="The image URL for the course."
 *     ),
 *     required={"title","category_id", "author", "description", "price"}
 * )
 * 
 * @OA\Schema(
 *     schema="SessionCourse",
 *     title="SessionCourse",
 *     description="Schema for a session of a course",
 *     @OA\Property(property="id", type="integer", format="int64", description="The unique identifier for the session"),
 *     @OA\Property(property="course_section_id", type="integer", format="int64", description="The ID of the course section"),
 *     @OA\Property(property="title", type="string", description="The title of the session"),
 *     @OA\Property(property="description", type="string", description="The description of the session"),
 *     @OA\Property(property="file_path", type="string", description="The file path of the session file"),
 *     @OA\Property(property="file_name", type="string", description="The file name of the session file"),
 *     @OA\Property(property="file_type", type="string", description="The file type of the session file"),
 *     @OA\Property(property="file_size", type="integer", format="int64", description="The file size of the session file in bytes"),
 *     @OA\Property(property="duration_minutes", type="integer", format="int64", description="The duration of the session in minutes"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="The datetime when the session was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="The datetime when the session was last updated"),
 * )
 * @OA\Schema(
 *     schema="CommentCourse",
 *     title="CommentCourse",
 *     description="Schema for a comment of a course",
 *     @OA\Property(property="id", type="integer", format="int64", description="The unique identifier for the session"),
 *     @OA\Property(property="course_id", type="integer", format="int64", description="The ID of the course"),
 *     @OA\Property(property="user_id", type="integer", description="The ID of the user"),
 *     @OA\Property(property="show", type="integer", description="show the comment(0 is hide ,1 is show)"),
 *     @OA\Property(property="comment", type="string", description="The comment text"),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="user_like", type="string", description="check if user like comment"),
 *     @OA\Property(
 *         property="likes",
 *         type="array",
 *         @OA\Items(
 *             type="object",
 *             @OA\Property(property="id", type="integer", format="int64", description="The ID of the user who liked the comment")
 *         )
 *     ),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="The datetime when the comment was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="The datetime when the comment was last updated"),
 * )
 *
 * 
 * @OA\Schema(
 *     schema="Notification",
 *     title="Notification",
 *     description="Schema for a notification",
 *     @OA\Property(property="id", type="integer", format="int64", description="The unique identifier for the notification"),
 *     @OA\Property(property="title", type="string", description="The title of the notification"),
 *     @OA\Property(property="content", type="string", description="The content of the notification"),
 *     @OA\Property(property="user_id", type="integer", format="int64", description="The ID of the user to whom the notification belongs"),
 *     @OA\Property(property="user", ref="#/components/schemas/User"),
 *     @OA\Property(property="read", type="boolean", description="Indicates whether the notification has been read or not"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="The datetime when the notification was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="The datetime when the notification was last updated"),
 * )
 *
 * @OA\Schema(
 *     schema="SectionCourse",
 *     title="SectionCourse",
 *     description="Schema for a section of a course",
 *     @OA\Property(property="id", type="integer", format="int64", description="The unique identifier for the section"),
 *     @OA\Property(property="course_id", type="integer", format="int64", description="The ID of the course"),
 *     @OA\Property(property="title", type="string", description="The title of the session"),
 *  *     @OA\Property(
 *         property="price",
 *         type="number",
 *         description="The price of the course."
 *     ),
 *     @OA\Property(
 *         property="discount",
 *         type="number",
 *         description="The discount applied to the course."
 *     ),
 *     @OA\Property(property="description", type="string", description="The description of the session"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="The datetime when the session was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="The datetime when the session was last updated"),
 * )
 * @OA\Schema(
 *     schema="Payment",
 *     title="Payment",
 *     description="Schema for a payment",
 *     @OA\Property(property="id", type="integer", format="int64", description="The unique identifier for the payment"),
 *     @OA\Property(property="user_id", type="integer", format="int64", description="The ID of the user making the payment"),
 *     @OA\Property(property="course_id", type="integer", format="int64", description="The ID of the course being paid for"),
 *     @OA\Property(property="paytype", type="string", description="The payment type ('section' or 'course')(course when used if user want to pay for course ,section when used if user want to pay for only a course section)"),
 *     @OA\Property(property="copoun_id", type="string", nullable=true, description="The ID of the coupon used for the payment"),
 *     @OA\Property(property="section_id", type="string", nullable=true, description="The ID of the section (optional)(it will be integer id of course section when user want to pay for only a section of course(paytype is equal to 'section'))(it will be null when user want to pay for course (paytype is equal to 'course'))"),
 *     @OA\Property(property="Authority", type="string", description="The authority of the payment"),
 *     @OA\Property(property="StartPay", type="string", description="The start page of the payment that links to the zarinpal page"),
 *     @OA\Property(property="refId", type="string", description="The reference id of the payment"),
 *     @OA\Property(property="pay", type="integer", format="int32", description="check if the payment succeeded(1 will be successful and 0 will be rejected)"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="The datetime when the payment was created"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="The datetime when the payment was last updated"),
 *    @OA\Property(property="section", ref="#/components/schemas/SectionCourse"),
 *    @OA\Property(property="course", ref="#/components/schemas/Course"),
 * )
 * 
 * * @OA\Schema(
 *     schema="Faqs",
 *     title="Faqs",
 *     description="Faq model",
 *     @OA\Property(
 *         property="id",
 *         type="integer",
 *         description="The unique identifier for the FAQ",
 *         example=1
 *     ),
 *     @OA\Property(
 *         property="question",
 *         type="string",
 *         description="The question of the FAQ",
 *         example="What is Laravel?"
 *     ),
 *     @OA\Property(
 *         property="answer",
 *         type="string",
 *         description="The answer of the FAQ",
 *         example="Laravel is a PHP framework for web development."
 *     ),
 *     @OA\Property(
 *         property="created_at",
 *         type="string",
 *         format="date-time",
 *         description="The date and time when the FAQ was created",
 *         example="2022-04-25 10:15:00"
 *     ),
 *     @OA\Property(
 *         property="updated_at",
 *         type="string",
 *         format="date-time",
 *         description="The date and time when the FAQ was last updated",
 *         example="2022-04-25 10:15:00"
 *     )
 * )
 */

abstract class Controller
{
 
}