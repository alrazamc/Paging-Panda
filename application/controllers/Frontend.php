<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Frontend Class
 *
 * @package   PHP_FMT
 * @subpackage  Controllers
 * @category  Frontend
 * @author    alrazamc
 * @link    http://phpfm.jcatpk.com
 */
class Frontend extends CI_Controller {

    public function __construct()
    {
        parent::__construct();
    }


    /** Landing Page */
    public function index()
    {
        if($this->session->userdata('user_login') === TRUE )
            redirect('content');
        $this->load->model('payments_model');
        $data['page_title'] = getenv('SITE_NAME') .' | Scheduling and Analytics Tool for Facebook Pages';
        $data['view'] = "home";
        $data['plans'] = $this->payments_model->get_all_plans();
        $this->load->view('frontend/front_template', $data);
    }

    //Contact Us
    public function contact()
    {
        $data['page_title'] = 'Contact Us | '.getenv('SITE_NAME');
        $data['view'] = "contact";
        $this->load->view('frontend/front_template', $data);
    }

    //Contact Us
    public function features()
    {
        $data['page_title'] = 'Features | '.getenv('SITE_NAME');
        $data['view'] = "features";
        $this->load->view('frontend/front_template', $data);
    }

    //Pricing
    public function pricing()
    {
        $data['page_title'] = 'Pricing | '.getenv('SITE_NAME');
        $data['view'] = "pricing";
        $this->load->model('payments_model');
        $data['plans'] = $this->payments_model->get_all_plans();
        $this->load->view('frontend/front_template', $data);
    }

    //privacy policy
    public function privacy()
    {
        $data['page_title'] = 'Privacy Policy | '.getenv('SITE_NAME');
        $data['view'] = "privacy";
        $this->load->view('frontend/front_template', $data);
    }

    //Terms and conditions
    public function terms()
    {
        $data['page_title'] = 'Terms & Conditions | '.getenv('SITE_NAME');
        $data['view'] = "terms";
        $this->load->view('frontend/front_template', $data);
    }

    //Contact Us
    public function inquiry()
    {
        $this->load->library('form_validation');
        $this->form_validation->set_rules('email', 'email address', 'trim|required|valid_email');
        $this->form_validation->set_rules('message', 'message', 'trim|required');
        if(!$this->form_validation->run())
        {
            if(validation_errors())
                $this->session->set_flashdata('error', validation_errors());
            redirect('contact');
        }else
        {
            $message = $this->input->post('message');
            $message = nl2br($message);
            if($this->input->post('name'))
                $message .= "<br> Sender: ".$this->input->post('name');
            $client = Aws\Ses\SesClient::factory(array(
                'credentials' => array(
                    'key' => getenv('AWS_KEY'),
                    'secret'  => getenv('AWS_SECRET'),
                  ),
                'region' => 'us-east-1',
                'version' => 'latest'
            ));

            try {
                 $result = $client->sendEmail([
                    'Destination' => [
                        'ToAddresses' => [
                            getenv('EMAIL_FROM')
                        ],
                    ],
                    'Message' => [
                        'Body' => [
                            'Html' => [
                                'Charset' => 'UTF-8',
                                'Data' => $message,
                            ],
                            'Text' => [
                                'Charset' => 'UTF-8',
                                'Data' => strip_tags($message),
                            ],
                        ],
                        'Subject' => [
                            'Charset' => 'UTF-8',
                            'Data' => getenv('SITE_NAME').' Inquiry',
                        ],
                    ],
                    'ReplyToAddresses' => [$this->input->post('email')],
                    'Source' => getenv('EMAIL_FROM')

                ]);
                 $messageId = $result->get('MessageId');
                 $this->session->set_flashdata('success', 'We received your message and will contact you back soon.');

            } catch (Aws\Ses\Exception\SesException $error) {
                //$error->getAwsErrorMessage();
                $this->session->set_flashdata('error', "Something went wrong while sending your inquiry. Please send your request using chat button in bottom right");
            }
            redirect('contact');
        }
    }

}

/* End of file Frontend.php */
/* Location: ./application/controllers/Frontend.php */
