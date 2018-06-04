<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Queue Maniuplation Algorithms, You should think about choosing another profession, if after 7 days, you have no idea what's going on here
 *
 */ 
class Queue {

    public function __construct()
    {
        $this->ci =& get_instance();
        $this->ci->load->model('queue_model');
        $this->include_in_random['0'] = '1'; //include general category in random queue
    }
    

    /**
    * Build Queue
    */
    public function rebuild($user_id = 0)
    {
        //Get Data
        $schedule = $this->ci->queue_model->get_slots($user_id);
        $library = $this->ci->queue_model->get_content($user_id);
        if(empty($library) || empty($schedule))
        {
            $this->ci->queue_model->remove_auto_scheduled_pending_posts($user_id);
            return;
        }
        $categories = $this->ci->queue_model->get_categories($user_id);
        $user = $this->ci->queue_model->get_user($user_id);

        $content[CATEGORY_GENERAL] = $content[CATEGORY_USE_ONCE] = $content[CATEGORY_RANDOM] = [];
        $content_counts[CATEGORY_GENERAL] = $content_counts[CATEGORY_USE_ONCE] = $content_counts[CATEGORY_RANDOM] = 0;

        $pointers[CATEGORY_RANDOM] = (int) $user->random_pointer;
        $pointers[CATEGORY_USE_ONCE] = (int) $user->use_once_pointer;
        $pointers[CATEGORY_GENERAL] = (int) $user->general_pointer;
        
        for($i=0; $i<7; $i++)
            $days[$i] = [];

        foreach ($categories as $category) {
            $content[  $category->category_id ] = [];
            $pointers[ $category->category_id ] = (int) $category->queue_pointer;
            $content_counts[$category->category_id] = 0;
            $this->include_in_random[$category->category_id] = $category->include_in_random;
        }
        foreach ($schedule as $slot)
        {
            $days[$slot->day][] = $slot;
        }

        //var_dump($days);die;
        foreach ($library as $item)
        {
            $item->accounts = json_decode($item->accounts);
            if($item->use_once == YES)
            {
                $item->published_on = json_decode($item->published_on);
                $item->accounts = array_diff($item->accounts, $item->published_on); //get pages where not published yet
                $item->category_id = CATEGORY_USE_ONCE;
            }
            $content[ $item->category_id ][] = $item;
            $content_counts[ $item->category_id ]++;
        }
        $random_content = $this->ci->queue_model->get_random_content($user_id);
        foreach ($random_content as $item)
        {
            $item->accounts = json_decode($item->accounts);
            $content[ CATEGORY_RANDOM ][$item->random_order] = $item;
            $content_counts[ CATEGORY_RANDOM ]++;
        }
        end($content[ CATEGORY_RANDOM ]);
        $random_end_index = key($content[ CATEGORY_RANDOM ]);

        reset($content[ CATEGORY_RANDOM ]);
        $random_start_index = key($content[ CATEGORY_RANDOM ]);
        $random_keys = array_keys($content[ CATEGORY_RANDOM ]);
        $pointers[CATEGORY_RANDOM] = empty($pointers[CATEGORY_RANDOM]) ? $random_start_index : $pointers[CATEGORY_RANDOM]; // if random pointer is zero set it to first rando index

        $posts_batch = [];

        $current_time_stamp = strtotime('now');

        for($index = 0; $index < 14; $index++)
        {
            $schedule_date = date('Y-m-d', strtotime("+$index days"));
            $week_day = date('N', strtotime("+$index days"));
            foreach ($days[$week_day == 7 ? 0 : $week_day] as $slot)
            {
                $schedule_time = "$schedule_date $slot->time";
                if($index == 0 && strtotime($schedule_time) < $current_time_stamp) //current day and scheduled slot is passed, skip to next slot
                    continue;
                if($slot->use_random == YES)
                    $slot->category_id = CATEGORY_RANDOM;
                else if($slot->use_once == YES)
                    $slot->category_id = CATEGORY_USE_ONCE;
                if(empty($content[$slot->category_id])) continue; //no content in the category, skip slot
                $slot_pages = json_decode($slot->accounts);
                while(!empty($slot_pages))
                {
                    $slot_page_matched = false;
                    $searched_items = 0;
                    do{
                        //round the pointer to front
                        if($slot->category_id == CATEGORY_RANDOM)
                            $pointers[ $slot->category_id ] = $pointers[ $slot->category_id ] >= ($random_end_index + 1) ? $random_start_index : $pointers[ $slot->category_id ];
                        else
                            $pointers[ $slot->category_id ] = $pointers[ $slot->category_id ] >= $content_counts[ $slot->category_id ] ? 0 : $pointers[ $slot->category_id ];
                        //get content at current pointer
                        if( isset($content[$slot->category_id][ $pointers[ $slot->category_id ]  ]) )
                            $content_item = $content[$slot->category_id][ $pointers[ $slot->category_id ]  ];
                        else
                            $content_item = false;
                        if($content_item === false)
                        {
                            $pointers[ $slot->category_id ]++; continue; //move to next index, content doesn't exist on this index
                        }
                        else if($this->_is_post_eligible($content_item, $slot, $schedule_time, $slot_pages, $current_time_stamp) === false)
                        {
                            if($slot->category_id == CATEGORY_RANDOM)
                            {
                                if($pointers[ $slot->category_id ] == $random_end_index) // last random key
                                    $pointers[ $slot->category_id ] = $random_start_index; // point to first key
                                else
                                    $pointers[ $slot->category_id ] = $random_keys[array_search($pointers[ $slot->category_id ],$random_keys)+1]; // get the next random key 
                            }else
                            {
                                $pointers[ $slot->category_id ]++;
                            }
                            
                            $searched_items ++;
                            continue;
                        }else
                        {
                            $slot_page_matched = true;
                            break;
                        }
                    }while(!$slot_page_matched && $searched_items != $content_counts[ $slot->category_id ]); //while no page of slot matched with content pages and all content of this category is not searched
                    
                    if($slot_page_matched === false) // no content found for any slot page in slot category, slot pages are different from pages linked to content in this category
                        break;
                    $matched_pages = array_intersect($slot_pages, $content_item->accounts); // get pages that matched slot pages
                    if(empty($matched_pages)) break;
                    reset($matched_pages);
                    $page_index = key($matched_pages); //get index of first matched paged
                    $post_on_page = $matched_pages[$page_index]; // select the first page from matched pages
                    $posts_batch[] = array( //create post
                            'user_id' => $user_id,
                            'content_id' => $content_item->content_id,
                            'account_id' => $post_on_page,
                            'schedule_id' => $slot->schedule_id,
                            'status' => POST_STATUS_PENDING,
                            'error_message' => '',
                            'scheduled_time' => $schedule_time,
                            'post_fb_id' => '',
                            'is_random' => $slot->use_random
                        );
                    
                    unset($slot_pages[$page_index]); //remove the page from slot pages as post for this page is created
                    $slot_pages = array_values($slot_pages); // reset remaining slot pages to find content for them
                    //move to next content item
                    if($slot->category_id == CATEGORY_RANDOM)
                    {
                        if($pointers[ $slot->category_id ] == $random_end_index)
                            $pointers[ $slot->category_id ] = $random_start_index;
                        else
                            $pointers[ $slot->category_id ] = $random_keys[array_search($pointers[ $slot->category_id ],$random_keys)+1];
                    }else
                    {
                        $pointers[ $slot->category_id ]++;
                    }
                } //move to next page of slot
            } //move to next slot of the day
        } // move to next date

        $this->ci->queue_model->remove_auto_scheduled_pending_posts($user_id);
        if(!empty($posts_batch))
            $this->ci->db->insert_batch('posts', $posts_batch);
    }

    /**
    * Rebuild queue of only specfic category
    */
    public function rebuild_category($user_id = 0, $category_id = 0)
    {
        //Get Data
        $schedule = $this->ci->queue_model->get_slots($user_id, $category_id);
        $library = $this->ci->queue_model->get_content($user_id, $category_id);
        if(empty($library) || empty($schedule))
        {
            $this->ci->queue_model->remove_auto_scheduled_pending_posts($user_id, $category_id);
            return;
        }

        $content[$category_id] = [];
        $content_counts[$category_id] = 0;

        if($category_id == CATEGORY_USE_ONCE || $category_id == CATEGORY_GENERAL)
        {
            $user = $this->ci->queue_model->get_user($user_id);
            $pointers[$category_id] = (int) $category_id == CATEGORY_USE_ONCE ? $user->use_once_pointer : $user->general_pointer;
        }else
        {
            $category = $this->ci->queue_model->get_category($category_id);
            $pointers[ $category_id ] = (int) $category->queue_pointer;
            $this->include_in_random[$category_id] = $category->include_in_random;
        }
        
        for($i=0; $i<7; $i++)
            $days[$i] = [];
        foreach ($schedule as $slot)
            $days[$slot->day][] = $slot;

        //var_dump($days);die;
        foreach ($library as $item)
        {
            $item->accounts = json_decode($item->accounts);
            if($item->use_once == YES)
            {
                $item->published_on = json_decode($item->published_on);
                $item->accounts = array_diff($item->accounts, $item->published_on); //get pages where not published yet
                $item->category_id = CATEGORY_USE_ONCE;
            }
            $content[ $item->category_id ][] = $item;
            $content_counts[ $item->category_id ]++;
        }

        $posts_batch = [];

        $current_time_stamp = strtotime('now');

        for($index = 0; $index < 14; $index++)
        {
            $schedule_date = date('Y-m-d', strtotime("+$index days"));
            $week_day = date('N', strtotime("+$index days"));
            foreach ($days[$week_day == 7 ? 0 : $week_day] as $slot)
            {
                $schedule_time = "$schedule_date $slot->time";
                if($index == 0 && strtotime($schedule_time) < $current_time_stamp) //current day and scheduled slot is passed, skip to next slot
                    continue;
                if($slot->use_random == YES)
                    $slot->category_id = CATEGORY_RANDOM;
                else if($slot->use_once == YES)
                    $slot->category_id = CATEGORY_USE_ONCE;
                if(empty($content[$slot->category_id])) continue; //no content in the category, skip slot
                $slot_pages = json_decode($slot->accounts);
                while(!empty($slot_pages))
                {
                    $slot_page_matched = false;
                    $searched_items = 0;
                    do{
                        //round the pointer to front
                        $pointers[ $slot->category_id ] = $pointers[ $slot->category_id ] >= $content_counts[ $slot->category_id ] ? 0 : $pointers[ $slot->category_id ];
                        //get content at current pointer
                        if( isset($content[$slot->category_id][ $pointers[ $slot->category_id ]  ]) )
                            $content_item = $content[$slot->category_id][ $pointers[ $slot->category_id ]  ];
                        else
                            $content_item = false;
                        if($content_item === false)
                        {
                            $pointers[ $slot->category_id ]++; continue; //move to next index, content doesn't exist on this index
                        }
                        else if($this->_is_post_eligible($content_item, $slot, $schedule_time, $slot_pages, $current_time_stamp) === false)
                        {
                            $pointers[ $slot->category_id ]++;
                            $searched_items ++;
                            continue;
                        }else
                        {
                            $slot_page_matched = true;
                            break;
                        }
                    }while(!$slot_page_matched && $searched_items != $content_counts[ $slot->category_id ]); //while no page of slot matched with content pages and all content of this category is not searched
                    
                    if($slot_page_matched === false) // no content found for any slot page in slot category, slot pages are different from pages linked to content in this category
                        break;
                    $matched_pages = array_intersect($slot_pages, $content_item->accounts); // get pages that matched slot pages
                    if(empty($matched_pages)) break;
                    reset($matched_pages);
                    $page_index = key($matched_pages); //get index of first matched paged
                    $post_on_page = $matched_pages[$page_index]; // select the first page from matched pages
                    $posts_batch[] = array( //create post
                            'user_id' => $user_id,
                            'content_id' => $content_item->content_id,
                            'account_id' => $post_on_page,
                            'schedule_id' => $slot->schedule_id,
                            'status' => POST_STATUS_PENDING,
                            'error_message' => '',
                            'scheduled_time' => $schedule_time,
                            'post_fb_id' => '',
                            'is_random' => $slot->use_random
                        );
                    
                    unset($slot_pages[$page_index]); //remove the page from slot pages as post for this page is created
                    $slot_pages = array_values($slot_pages); // reset remaining slot pages to find content for them
                    //move to next content item
                    $pointers[ $slot->category_id ]++;
                } //move to next page of slot
            } //move to next slot of the day
        } // move to next date

        $this->ci->queue_model->remove_auto_scheduled_pending_posts($user_id, $category_id);
        if(!empty($posts_batch))
            $this->ci->db->insert_batch('posts', $posts_batch);
    }

    /**
    * Rebuild only random Queue
    */
    public function rebuild_random($user_id = 0)
    {
        //Get Data
        $schedule = $this->ci->queue_model->get_slots($user_id, CATEGORY_RANDOM); //get only random slots from schedule
        $random_content = $this->ci->queue_model->get_random_content($user_id);
        if(empty($random_content) || empty($schedule)) //no content or no random slots in schedule
        {
            //delete only randomly selected posts in queue 
            $this->ci->queue_model->remove_auto_scheduled_pending_posts($user_id, CATEGORY_RANDOM);
            return;
        }
        
        $categories = $this->ci->queue_model->get_categories($user_id);
        foreach ($categories as $category)
            $this->include_in_random[$category->category_id] = $category->include_in_random;

        $user = $this->ci->queue_model->get_user($user_id);
        $content[CATEGORY_RANDOM] = [];
        $content_counts[CATEGORY_RANDOM] = 0;
        $pointers[CATEGORY_RANDOM] = (int) $user->random_pointer;
        
        for($i=0; $i<7; $i++)
            $days[$i] = [];
        foreach ($schedule as $slot)
            $days[$slot->day][] = $slot;

        foreach ($random_content as $item)
        {
            $item->accounts = json_decode($item->accounts);
            $content[ CATEGORY_RANDOM ][$item->random_order] = $item;
            $content_counts[ CATEGORY_RANDOM ]++;
        }
        end($content[ CATEGORY_RANDOM ]);
        $random_end_index = key($content[ CATEGORY_RANDOM ]);

        reset($content[ CATEGORY_RANDOM ]);
        $random_start_index = key($content[ CATEGORY_RANDOM ]);
        $random_keys = array_keys($content[ CATEGORY_RANDOM ]);
        $pointers[CATEGORY_RANDOM] = empty($pointers[CATEGORY_RANDOM]) ? $random_start_index : $pointers[CATEGORY_RANDOM]; // if random pointer is zero set it to first rando index

        $posts_batch = [];

        $current_time_stamp = strtotime('now');

        for($index = 0; $index < 14; $index++) // build queue for next 2 weeks(14 days)
        {
            $schedule_date = date('Y-m-d', strtotime("+$index days"));
            $week_day = date('N', strtotime("+$index days"));
            foreach ($days[$week_day == 7 ? 0 : $week_day] as $slot)
            {
                $schedule_time = "$schedule_date $slot->time";
                if($index == 0 && strtotime($schedule_time) < $current_time_stamp) //current day and scheduled slot is passed, skip to next slot
                    continue;
                
                $slot->category_id = CATEGORY_RANDOM; //only random slots here, building random queue

                if(empty($content[$slot->category_id])) continue; //no content in the category, skip slot
                $slot_pages = json_decode($slot->accounts);
                while(!empty($slot_pages))
                {
                    $slot_page_matched = false;
                    $searched_items = 0;
                    do{
                        $pointers[ $slot->category_id ] = $pointers[ $slot->category_id ] >= ($random_end_index + 1) ? $random_start_index : $pointers[ $slot->category_id ];
                        //get content at current pointer
                        if( isset($content[$slot->category_id][ $pointers[ $slot->category_id ]  ]) )
                            $content_item = $content[$slot->category_id][ $pointers[ $slot->category_id ]  ];
                        else
                            $content_item = false;
                        if($content_item === false)
                        {
                            $pointers[ $slot->category_id ]++; continue; //move to next index, content doesn't exist on this index
                        }
                        else if($this->_is_post_eligible($content_item, $slot, $schedule_time, $slot_pages, $current_time_stamp) === false)
                        {
                            if($pointers[ $slot->category_id ] == $random_end_index) // last random key
                                $pointers[ $slot->category_id ] = $random_start_index; // point to first key
                            else
                                $pointers[ $slot->category_id ] = $random_keys[array_search($pointers[ $slot->category_id ],$random_keys)+1]; // get the next random key 
                            $searched_items ++;
                            continue;
                        }else
                        {
                            $slot_page_matched = true;
                            break;
                        }
                    }while(!$slot_page_matched && $searched_items != $content_counts[ $slot->category_id ]); //while no page of slot matched with content pages and all content of this category is not searched
                    
                    if($slot_page_matched === false) // no content found for any slot page in slot category, slot pages are different from pages linked to content in this category
                        break;
                    $matched_pages = array_intersect($slot_pages, $content_item->accounts); // get pages that matched slot pages
                    if(empty($matched_pages)) break;
                    reset($matched_pages);
                    $page_index = key($matched_pages); //get index of first matched paged
                    $post_on_page = $matched_pages[$page_index]; // select the first page from matched pages
                    $posts_batch[] = array( //create post
                            'user_id' => $user_id,
                            'content_id' => $content_item->content_id,
                            'account_id' => $post_on_page,
                            'schedule_id' => $slot->schedule_id,
                            'status' => POST_STATUS_PENDING,
                            'error_message' => '',
                            'scheduled_time' => $schedule_time,
                            'post_fb_id' => '',
                            'is_random' => $slot->use_random
                        );
                    
                    unset($slot_pages[$page_index]); //remove the page from slot pages as post for this page is created
                    $slot_pages = array_values($slot_pages); // reset remaining slot pages to find content for them
                    //move to next content item
                    if($pointers[ $slot->category_id ] == $random_end_index)
                        $pointers[ $slot->category_id ] = $random_start_index;
                    else
                        $pointers[ $slot->category_id ] = $random_keys[array_search($pointers[ $slot->category_id ],$random_keys)+1];
                } //move to next page of slot
            } //move to next slot of the day
        } // move to next date

        $this->ci->queue_model->remove_auto_scheduled_pending_posts($user_id, CATEGORY_RANDOM);
        if(!empty($posts_batch))
            $this->ci->db->insert_batch('posts', $posts_batch);
    }

    private function _is_post_eligible($content_item, $slot, $schedule_time, $slot_pages, $current_time_stamp)
    {
        if($slot->use_random == YES && ($content_item->use_once == YES || $this->include_in_random[$content_item->category_id] == '0'  ) ) // random slot and content is use once or content category isn't included in random
            return false;
        if($content_item->use_once == YES && empty($content_item->accounts))
            return false;
        if($content_item->should_expire == YES && strtotime($content_item->expiry_time) < strtotime($schedule_time)) // if content will expire at schedule time
            return false;
        if($content_item->is_scheduled == YES && strtotime($content_item->scheduled_time) > $current_time_stamp) //content scheduled and scheduled time not passed yet
            return false;
        if(count(array_diff($slot_pages, $content_item->accounts)) == count($slot_pages)) //or if this content isn't linked to any page of slot, skip content
            return false;

        return true;
    }

}